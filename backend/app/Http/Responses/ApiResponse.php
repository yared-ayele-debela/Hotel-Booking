<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function error(string $message, int $status = 422, ?string $code = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code ?? 'ERROR',
        ], $status);
    }

    public static function validationErrors(array $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => 'VALIDATION_ERROR',
            'errors' => $errors,
        ], 422);
    }

    public static function success($data = null, int $status = 200): JsonResponse
    {
        $body = ['success' => true];
        if ($data !== null) {
            $body['data'] = $data;
        }
        return response()->json($body, $status);
    }
}
