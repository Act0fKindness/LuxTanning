<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function ok(array $data = [], int $status = 200): JsonResponse
    {
        return response()->json(['ok' => true] + $data, $status);
    }

    protected function notImplemented(string $message = 'Not implemented'): JsonResponse
    {
        return response()->json(['ok' => false, 'error' => $message], 501);
    }
}

