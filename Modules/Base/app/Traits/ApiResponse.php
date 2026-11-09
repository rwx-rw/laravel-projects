<?php

namespace Modules\Base\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function successResponse(?string $message = null, array $data = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function errorResponse(?string $message = null, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
