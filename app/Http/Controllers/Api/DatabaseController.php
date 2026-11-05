<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseController extends BaseApiController
{
    public function export(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $data = [
            'tenant' => Tenant::find($tenantId),
            'categories' => Category::where('tenant_id', $tenantId)->get(),
            'units' => Unit::where('tenant_id', $tenantId)->get(),
            'suppliers' => Supplier::where('tenant_id', $tenantId)->get(),
            // Include category/unit/supplier name for re-mapping on import
            'products' => Product::with(['category:id,slug', 'unit:id,name', 'supplier:id,name'])
                ->where('tenant_id', $tenantId)->get(),
            // Exclude password hash and sensitive tokens from backup
            'users' => User::where('tenant_id', $tenantId)
                ->get(['id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at']),
            'export_date' => now()->toDateTimeString(),
            'version' => '1.0',
        ];

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="restoku_backup_'.date('Y-m-d_H-i').'.json"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:10240', // max 10MB
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($json, true);

        if (! $data || ! isset($data['tenant'])) {
            return $this->errorResponse('Invalid backup file format.', 422);
        }

        $tenantId = $request->user()->tenant_id;

        try {
            DB::beginTransaction();

            // 1. Categories
            if (isset($data['categories'])) {
                foreach ($data['categories'] as $cat) {
                    Category::updateOrCreate(
                        ['tenant_id' => $tenantId, 'slug' => $cat['slug']],
                        collect($cat)->except(['id', 'tenant_id', 'created_at', 'updated_at', 'deleted_at'])->toArray()
                    );
                }
            }

            // 2. Units
            if (isset($data['units'])) {
                foreach ($data['units'] as $unit) {
                    Unit::updateOrCreate(
                        ['tenant_id' => $tenantId, 'name' => $unit['name']],
                        collect($unit)->except(['id', 'tenant_id', 'created_at', 'updated_at', 'deleted_at'])->toArray()
                    );
                }
            }

            // 3. Suppliers
            if (isset($data['suppliers'])) {
                foreach ($data['suppliers'] as $sup) {
                    Supplier::updateOrCreate(
                        ['tenant_id' => $tenantId, 'name' => $sup['name']],
                        collect($sup)->except(['id', 'tenant_id', 'created_at', 'updated_at', 'deleted_at'])->toArray()
                    );
                }
            }

            // 4. Products
            if (isset($data['products'])) {
                // Refresh maps for IDs
                $catMap = Category::where('tenant_id', $tenantId)->pluck('id', 'slug')->toArray();
                $unitMap = Unit::where('tenant_id', $tenantId)->pluck('id', 'name')->toArray();
                $supMap = Supplier::where('tenant_id', $tenantId)->pluck('id', 'name')->toArray();

                foreach ($data['products'] as $p) {
                    $pData = collect($p)->except([
                        'id', 'tenant_id', 'category_id', 'unit_id', 'supplier_id',
                        'created_at', 'updated_at', 'deleted_at', 'category', 'unit', 'supplier',
                    ])->toArray();

                    // Re-map relasi berdasarkan slug/name yang disimpan di backup v1.0+
                    if (! empty($p['category']['slug'])) {
                        $pData['category_id'] = $catMap[$p['category']['slug']] ?? null;
                    }
                    if (! empty($p['unit']['name'])) {
                        $pData['unit_id'] = $unitMap[$p['unit']['name']] ?? null;
                    }
                    if (! empty($p['supplier']['name'])) {
                        $pData['supplier_id'] = $supMap[$p['supplier']['name']] ?? null;
                    }

                    Product::updateOrCreate(
                        ['tenant_id' => $tenantId, 'code' => $p['code']],
                        $pData
                    );
                }
            }

            DB::commit();

            return $this->successResponse(null, 'Database imported successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Import failed: '.$e->getMessage(), 500);
        }
    }
}
