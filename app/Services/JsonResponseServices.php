<?php

namespace App\Services;

final class JsonResponseServices
{
    public static function getApiValidationFailedResponse($errors)
    {
        return response()->json([
            'data' => [],
            'status' => config('constants.FALSE_STATUS'),
            'message' => $errors,
        ], config('constants.HTTP_UNPROCESSABLE_REQUEST'));
    }

    public static function getApiResponse($data, $status, $message, $httpCode)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => ($httpCode == 500) ? $message->getMessage() : $message
        ], $httpCode);
    }

    public static function getApiResponseExtention($data, $status, $message, $extraKey, $extraKeyData, $httpCode)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => ($httpCode == 500) ? $message->getMessage() : $message,
            $extraKey => $extraKeyData
        ], $httpCode);
    }
}
