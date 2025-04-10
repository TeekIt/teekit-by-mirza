<?php

namespace App\Http\Controllers;

use App\Categories;
use App\Products;
use App\Qty;
use App\Services\GoogleMapServices;
use App\Services\ImageServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $validatedData = Validator::make($request->all(), [
            'categoryName' => 'required|string|max:255',
            'categoryImage' => 'required|image|mimes:jpeg,png,jpg|max:100',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $category = Categories::add(
            $validatedData->categoryName,
            ImageServices::uploadImg($request, "categoryImage")
        );

        return JsonResponseServices::getApiResponse(
            $category,
            config('constants.TRUE_STATUS'),
            config('constants.DATA_INSERTION_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * Update category
     * @version 1.2.0
     */
    public function update(Request $request, $category_id)
    {
        // $validatedData = Categories::validator($request);
        // if ($validatedData->fails()) {
        //     return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        // }
        $category = Categories::updateCategory($request, $category_id);

        return JsonResponseServices::getApiResponse(
            $category,
            config('constants.TRUE_STATUS'),
            config('constants.DATA_UPDATED_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }

    public function destroy(Request $request)
    {
        for ($i = 0; $i < count($request->categories); $i++) {
            Categories::where('id', '=', $request->categories[$i])->delete();
        }

        return response("Categories Deleted Successfully");
    }
    /**
     * List all categories w.r.t store ID or without store ID
     * @version 1.2.0
     */
    public function all(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'store_id' => 'integer',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        if ($request->store_id)
            $data =  Categories::getAllCategoriesBySellerId(
                $request->store_id,
                ['id as category_id', 'category_name', 'category_image']
            );
        else
            $data = Cache::rememberForever(
                'allCategories',
                fn() => Categories::allCategories([
                    'id as category_id',
                    'category_name',
                    'category_image'
                ])
            );
        /*
        * Just creating this variable so we don't have to call the "isEmpty()" function again & again
        * Which will obviouly increase the API response speed
        */
        $data_is_empty = $data->isEmpty();
        return JsonResponseServices::getApiResponse(
            ($data_is_empty) ? [] : $data,
            ($data_is_empty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($data_is_empty) ? config('constants.NO_RECORD') : '',
            config('constants.HTTP_OK')
        );
    }
    /**
     * It will get the products of a specific category
     * @version 1.9.0
     */
    public function productsByCategory(Request $request)
    {
        $validatedData = Validator::make(
            array_merge($request->route()->parameters(), $request->query()),
            [
                'categoryId' => 'required|integer',
                'sellerId' => 'required|integer',
                'page' => 'required|integer',
            ]
        );
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        // if ($request->sellerId)
        //      $data = Qty::getProductsByGivenIds($request->categoryId, $request->sellerId);
        // else
        //     $data = Categories::getProducts($request->categoryId);

        $pagination = Cache::remember(
            'productsByCategory' . $request->categoryId . $request->sellerId . $request->page,
            now()->addDay(),
            function () use ($request) {
                return Products::getProductsInfoByCategoryId(
                    $request->categoryId,
                    $request->sellerId,
                    Products::getCommonColumns(),
                )->toArray();
            }
        );

        $data = $pagination['data'];
        unset($pagination['data']);

        /*
        * Just creating this variable so we don't have to call the "empty()" function again & again
        * Which will obviouly increase the API response speed
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
     * @version 1.0.0
     */
    public function sellers(Request $request)
    {
        $validatedData = Validator::make($request->query(), [
            'category_id' => 'required|integer',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'state' => 'required|string'
            // 'page' => 'required|numeric'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $sellers = Cache::remember(
            'get-sellers-by-category' . $validatedData->category_id . $validatedData->lat . $validatedData->lon,
            now()->addDay(),
            function () use ($validatedData) {
                return Qty::getSellersByGivenParams($validatedData->category_id, $validatedData->state);
            }
        );

        $data = GoogleMapServices::findNearByUsersByMakingChunks($validatedData->lat, $validatedData->lon, $sellers, 25);
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
