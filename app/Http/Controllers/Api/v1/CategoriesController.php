<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Categories;
use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Qty;
use App\Services\GoogleMapServices;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{
    /**
     * List all categories w.r.t store ID or without store ID
     *
     * @version 1.2.0
     */
    public function list(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'sellerId' => 'integer',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        if (isset($validatedData->sellerId)) {
            $data = Categories::getAllCategoriesBySellerId(
                $validatedData->sellerId,
                ['id', 'category_name', 'category_image']
            );
        } else {
            $data = Cache::rememberForever(
                'categoriesList',
                fn() => Categories::getAll([
                    'id',
                    'category_name',
                    'category_image',
                ])
            );
        }
        /*
        * Just creating this variable so we don't have to call the "isEmpty()" function again & again
        * Because it will increase the API response time
        */
        $dataIsEmpty = $data->isEmpty();

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * It will get the products of a specific category
     *
     * @version 1.9.0
     */
    public function listProductsByCategoryId(Request $request)
    {
        $validatedData = Validator::make(
            array_merge($request->route()->parameters(), $request->query()),
            [
                'categoryId' => 'required|integer|exists:categories,id',
                'sellerId' => 'required|integer|exists:users,id',
                'page' => 'required|integer',
            ]
        );
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $pagination = Cache::remember(
            'productsByCategory' . $validatedData->categoryId . $validatedData->sellerId . $validatedData->page,
            now()->addDay(),
            function () use ($validatedData) {
                return Products::getProductsInfoByCategoryId(
                    $validatedData->categoryId,
                    $validatedData->sellerId,
                    Products::getCommonColumns(),
                )->toArray();
            }
        );

        $data = $pagination['data'];
        unset($pagination['data']);

        /*
        * Just creating this variable so we don't have to call the "empty()" function again & again
        * Because it will increase the API response time
        */
        $dataIsEmpty = empty($data);

        return JsonResponseServices::getApiResponseExtention(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            'pagination',
            ($dataIsEmpty) ? (object) [] : $pagination,
            config('constants.HTTP_OK')
        );
    }

    /**
     * It will get the sellers w.r.t category id
     *
     * @version 1.0.0
     */
    public function listSellersByCategoryId(Request $request)
    {
        $validatedData = Validator::make(
            array_merge($request->route()->parameters(), $request->query()),
            [
                'categoryId' => 'required|integer|exists:categories,id',
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
                'city' => 'required|string',
            ]
        );
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $data = Cache::remember(
            'listSellersByCategoryId' . $validatedData->categoryId . $validatedData->lat . $validatedData->lon,
            now()->addDay(),
            function () use ($validatedData) {
                $sellers = Qty::getSellersByGivenParams($validatedData->categoryId, $validatedData->city);

                return GoogleMapServices::findNearByUsersByMakingChunks(
                    $validatedData->lat,
                    $validatedData->lon,
                    $sellers,
                    25
                );
            }
        );
        /*
        * Just creating this variable so we don't have to call the "empty()" function again & again
        * Because it will increase the API response time
        */
        $dataIsEmpty = empty($data);

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $data,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            config('constants.HTTP_OK')
        );
    }
}
