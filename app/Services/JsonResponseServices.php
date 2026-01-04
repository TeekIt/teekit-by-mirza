<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

final class JsonResponseServices
{
    private static function getMessage(mixed $message, int $httpCode): string
    {
        return ($httpCode == config('constants.HTTP_SERVER_ERROR')) ? $message->getMessage() : $message;
    }

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
            'message' => self::getMessage($message, $httpCode),
        ], $httpCode);
    }

    public static function getPaginatedApiResponse(LengthAwarePaginator $data, mixed $message, int $httpCode): JsonResponse
    {
        $pagination = $data->toArray();
        /* Extract data from pagination array */
        $data = $pagination['data'];
        unset($pagination['data']);
        /*
         * Just creating this variable so we don't have to call the "empty()" function again & again
         * Because it will increase the API response time
         */
        $dataIsEmpty = empty($data);

        return response()->json([
            'data' => ($dataIsEmpty) ? [] : $data,
            'status' => ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            'message' => ($dataIsEmpty) ? config('constants.NO_RECORD') : self::getMessage($message, $httpCode),
            'pagination' => ($dataIsEmpty) ? (object) [] : $pagination,
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
            'message' => self::getMessage($message, $httpCode),
            $extraKey => $extraKeyData,
        ], $httpCode);
    }
}
