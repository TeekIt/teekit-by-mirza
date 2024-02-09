<?php

namespace App\Services;

final class JsonResponseCustom
{
    public static function getApiValidationFailedResponse($errors)
    {
        return response()->json([
            'data' => [],
            'status' => config('constants.FALSE_STATUS'),
            'message' => $errors,
        ], config('constants.HTTP_UNPROCESSABLE_REQUEST'));
    }

    public static function getApiResponse($data, $status, $message, $http_code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => ($http_code == 500) ? $message->getMessage() : $message
        ], $http_code);
    }

    public static function getApiResponseExtention($data, $status, $message, $extra_key, $extra_key_data, $http_code)
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => ($http_code == 500) ? $message->getMessage() : $message,
            $extra_key => $extra_key_data
        ], $http_code);
    }

    public static function getWebResponse($status, $message)
    {
        ($status) ? flash($message)->success() : flash($message)->error();
    }
}
