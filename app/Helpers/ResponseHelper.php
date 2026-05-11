<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Standard JSON Response.
     */
    public static function jsonResponse(bool $success, string $message, mixed $data = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }
    public static function success($data = null, $message = 'Request successful', $statusCode = 200): JsonResponse
    {
        return self::jsonResponse(true, $message, $data, $statusCode);
    }

    /**
     * Opsional: Tambahkan shortcut khusus error
     */
    public static function error($message = 'Request failed', $statusCode = 400, $data = null): JsonResponse
    {
        return self::jsonResponse(false, $message, $data, $statusCode);
    }
}
