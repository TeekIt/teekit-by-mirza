<?php

namespace App\Http\Controllers;

use App\Products;
use App\Services\GoogleMapServices;
use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Http\Request;
use App\Services\JsonResponseServices;
use App\Services\WebResponseServices;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * @author Mirza Abdullah Izhar
     */
    public function updateSellerRequiredInfo(Request $request)
    {
        $request->validate([
            'stripe_account_id' => 'required|string',
            'time' => 'required|array',
        ]);

        $time = $request->time;
        foreach ($time as $key => $value) {
            if (!in_array("on", $time[$key])) $time[$key] += ["closed" => null];
        }
        $business_hours['time'] = $time;
        $business_hours['submitted'] = "yes";

        $updated = User::updateInfo(
            auth()->id(),
            $business_hours,
            $request->stripe_account_id
        );
        if ($updated) {
            return WebResponseServices::getResponseRedirectBack(
                config('constants.SUCCESS_STATUS'),
                config('constants.UPDATION_SUCCESS')
            );
        }
        return WebResponseServices::getResponseRedirectBack(
            config('constants.ERROR_STATUS'),
            config('constants.UPDATION_FAILED')
        );
    }
    /**
     * Fetch seller information w.r.t ID
     * @author Mirza Abdullah Izhar
     * @version 2.1.0
     */
    public static function getSellerInfo(object $seller_info, array $map_api_result = null)
    {
        $data = array(
            'id' => $seller_info->id,
            'name' => $seller_info->name,
            'email' => $seller_info->email,
            'business_name' => $seller_info->business_name,
            'business_hours' => $seller_info->business_hours,
            'full_address' => $seller_info->full_address,
            'unit_address' => $seller_info->unit_address,
            'country' => $seller_info->country,
            'state' => $seller_info->state,
            'city' => $seller_info->city,
            'postcode' => $seller_info->postcode,
            'lat' => $seller_info->lat,
            'lon' => $seller_info->lon,
            'user_img' => $seller_info->user_img,
            'pending_withdraw' => $seller_info->pending_withdraw,
            'total_withdraw' => $seller_info->total_withdraw,
            'parent_store_id' => $seller_info->parent_store_id,
            'is_online' => $seller_info->is_online,
            'roles' => ($seller_info->role_id == 2) ? ['sellers'] : ['child_sellers'],
            'stripe_account_id' => $seller_info->stripe_account_id,
        );
        if (!empty($map_api_result)) {
            $data['distance'] = $map_api_result['distance'];
            $data['duration'] = $map_api_result['duration'];
        }
        return $data;
    }

    public function updateStoreLocation(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'full_address' => 'required|string',
                'unit_address' => 'nullable|string',
                'postcode' => 'required|string',
                'country' => 'required|string',
                'state' => 'required|string',
                'city' => 'required|string',
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180'
            ]);
            if ($validate->fails()) return WebResponseServices::getValidationResponseRedirectBack($validate);

            $updated = User::updateStoreLocation(
                Auth::id(),
                $request->full_address,
                $request->unit_address,
                $request->country,
                $request->state,
                $request->city,
                $request->postcode,
                $request->lat,
                $request->lon
            );
            if ($updated) {
                return WebResponseServices::getResponseRedirectBack(
                    config('constants.SUCCESS_STATUS'),
                    config('constants.UPDATION_SUCCESS')
                );
            }
            return WebResponseServices::getResponseRedirectBack(
                config('constants.ERROR_STATUS'),
                config('constants.UPDATION_FAILED')
            );
        } catch (Throwable $error) {
            report($error);
            return WebResponseServices::getResponseRedirectBack(
                config('constants.ERROR_STATUS'),
                $error->getMessage()
            );
        }
    }
    /**
     * Listing of all Sellers/Stores within 5 miles
     * @author Mirza Abdullah Izhar
     */
    public function sellers(Request $request)
    {
        try {
            $validated_data = Validator::make($request->query(), [
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
                'state' => 'required|string',
                'page' => 'required|numeric'
            ]);
            if ($validated_data->fails()) {
                return JsonResponseServices::getApiValidationFailedResponse($validated_data->errors());
            }
            $sellers = User::getParentAndChildSellersByState($request->state);
            $pagination = $sellers->toArray();
            unset($pagination['data']);

            if (!$sellers->isEmpty()) $data = GoogleMapServices::findDistanceByMakingChunks($request->lat, $request->lon, $sellers, 25);

            if (empty($data)) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_STORES_FOUND'),
                    config('constants.HTTP_OK')
                );
            }

            return JsonResponseServices::getApiResponseExtention(
                $data,
                config('constants.TRUE_STATUS'),
                '',
                'pagination',
                $pagination,
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
     * Search products w.r.t Seller/Store 'id' & Product Name
     * @author Mirza Abdullah Izhar
     * @version 1.4.0
     */
    public function searchSellerProducts($seller_id, $product_name)
    {
        try {
            $data = [];
            $article = Products::search($product_name)
                ->where('user_id', $seller_id)
                ->where('status', 1);
            $products = $article->paginate(20);
            $pagination = $products->toArray();
            if (!$products->isEmpty()) {
                foreach ($products as $product) {
                    $data[] = Products::getProductInfo($seller_id, $product->id, ['*']);
                }
                unset($pagination['data']);
                return JsonResponseServices::getApiResponseExtention(
                    $data,
                    config('constants.TRUE_STATUS'),
                    '',
                    'pagination',
                    $pagination,
                    config('constants.HTTP_OK')
                );
            }

            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_RECORD'),
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
