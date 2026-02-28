<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

abstract class BaseApiController extends Controller
{
    protected function success($data = null, int $status = 200)
    {
        return ApiResponse::success($data, $status);
    }

    protected function error(string $message, int $status = 422, ?string $code = null)
    {
        return ApiResponse::error($message, $status, $code);
    }
}
