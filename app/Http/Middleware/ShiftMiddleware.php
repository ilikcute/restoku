<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShiftMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin & Manager boleh akses tanpa shift (misalnya buat laporan)
        if ($user->hasRole(['admin', 'manager'])) {
            return $next($request);
        }

        // Cashier harus punya shift aktif
        $activeShift = Shift::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'open')
            ->first();

        if (! $activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum membuka shift hari ini. Silakan buka shift terlebih dahulu.',
            ], 422);
        }

        // Simpan shift ke request agar controller tidak query lagi
        $request->attributes->set('current_shift', $activeShift);

        return $next($request);
    }
}
