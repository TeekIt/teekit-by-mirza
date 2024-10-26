<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Qty;
use App\Services\GoogleMapServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Services\JsonResponseServices;
use Illuminate\Support\Facades\Cache;

class CategoriesController extends Controller
{
    /**
     * Insert's new categories
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function add(Request $request)
    {
        try {
            $validated_data = Categories::validator($request);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->errors());
            }
            $category = Categories::add($request);
            return JsonResponseServices::getApiResponse(
                $category,
                config('constants.TRUE_STATUS'),
                config('constants.DATA_INSERTION_SUCCESS'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * Update category
     * @version 1.2.0
     */
    public function update(Request $request, $category_id)
    {
        try {
            $validated_data = Categories::validator($request);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->errors());
            }
            $category = Categories::updateCategory($request, $category_id);
            return JsonResponseServices::getApiResponse(
                $category,
                config('constants.TRUE_STATUS'),
                config('constants.DATA_UPDATED_SUCCESS'),
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * List all categories w.r.t store ID or without store ID
     * @version 1.2.0
     */
    public function all(Request $request)
    {
        try {
            $validated_data = Validator::make($request->all(), [
                'store_id' => 'integer',
            ]);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->errors());
            }

            if ($request->store_id)
                $data =  Categories::getAllCategoriesByStoreId($request->store_id, ['id as category_id', 'category_name', 'category_image']);
            else
                $data = Cache::rememberForever('allCategories', fn() => Categories::allCategories(['id as category_id', 'category_name', 'category_image']));
            /*
            * Just creating this variable so we don't have to call the "isEmpty()" function again & again
            * Which will obviouly reduce the API response speed
            */
            $data_is_empty = $data->isEmpty();
            return JsonResponseServices::getApiResponse(
                ($data_is_empty) ? [] : $data,
                ($data_is_empty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
                ($data_is_empty) ? config('constants.NO_RECORD') : '',
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It will get the products of a specific category
     * @version 1.9.0
     */
    public function products(Request $request)
    {
        try {
            $validatedData = Validator::make($request->route()->parameters(), [
                'category_id' => 'required|integer',
                'store_id' => 'integer',
            ]);
            if ($validatedData->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            if ($request->store_id)
                $data = Qty::getProductsByGivenIds($request->category_id, $request->store_id);
            else
                $data = Categories::getProducts($request->category_id);
            
            /*
            * Just creating this variable so we don't have to call the "empty()" function again & again
            * Which will obviouly reduce the API response speed
            */
            $data_is_empty = empty($data);
            return JsonResponseServices::getApiResponseExtention(
                ($data_is_empty) ? [] : $data['data'],
                ($data_is_empty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
                ($data_is_empty) ? config('constants.NO_RECORD') : '',
                'pagination',
                ($data_is_empty) ? [] : $data['pagination'],
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
    /**
     * It will get the stores w.r.t category id
     * @version 1.0.0
     */
    public function stores(Request $request)
    {
        try {
            $validated_data = Validator::make($request->query(), [
                'category_id' => 'required|integer',
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
                'state' => 'required|string'
                // 'page' => 'required|numeric'
            ]);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->errors());
            }

            $stores = Cache::remember('get-stores-by-category' . $request->category_id . $request->lat . $request->lon, now()->addDay(), function () use ($request) {
                return Qty::getSellersByGivenParams($request->category_id, $request->state);
            });

            // $stores = Categories::stores($request->category_id, $request->city);
            // $pagination = $stores->toArray();
            // unset($pagination['data']);

            $data = GoogleMapServices::findDistanceByMakingChunks($request->lat, $request->lon, $stores, 25);
            /*
            * Just creating this variable so we don't have to call the "empty()" function again & again
            * Because it will increase the API response time
            */
            $data_is_empty = empty($data);
            // return JsonResponseServices::getApiResponseExtention(
            //     ($data_is_empty) ? [] : $data,
            //     ($data_is_empty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            //     ($data_is_empty) ? config('constants.NO_RECORD') : '',
            //     'pagination',
            //     ($data_is_empty) ? [] : $pagination,
            //     config('constants.HTTP_OK')
            // );

            return JsonResponseServices::getApiResponse(
                ($data_is_empty) ? [] : $data,
                ($data_is_empty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
                ($data_is_empty) ? config('constants.NO_RECORD') : '',
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
