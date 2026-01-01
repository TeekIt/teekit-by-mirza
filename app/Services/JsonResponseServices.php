<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

final class JsonResponseServices
{
    public static function getApiValidationFailedResponse(mixed $errors): JsonResponse
    {
        return response()->json([
            'data' => [],
            'status' => config('constants.FALSE_STATUS'),
            'message' => $errors,
        ], config('constants.HTTP_UNPROCESSABLE_REQUEST'));
    }

    public static function getApiResponse(mixed $data, bool $status, mixed $message, int $httpCode): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => ($httpCode == config('constants.HTTP_SERVER_ERROR')) ? $message->getMessage() : $message,
        ], $httpCode);
    }

    public static function getApiResponseExtention(
        mixed $data,
        bool $status,
        mixed $message,
        mixed $extraKey,
        mixed $extraKeyData,
        int $httpCode
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => ($httpCode == config('constants.HTTP_SERVER_ERROR')) ? $message->getMessage() : $message,
            $extraKey => $extraKeyData,
        ], $httpCode);
    }
}
