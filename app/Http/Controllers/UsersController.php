<?php

namespace App\Http\Controllers;

use App\Services\GoogleMap;
use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Services\JsonResponseCustom;
use App\Services\WebResponseCustom;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
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
                'lat' => 'required|numeric',
                'lon' => 'required|numeric'
            ]);
            if ($validate->fails()) return WebResponseCustom::getValidationResponseRedirectBack($validate);

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
                return WebResponseCustom::getResponseRedirectBack(
                    config('constants.SUCCESS_STATUS'),
                    config('constants.UPDATION_SUCCESS')
                );
            }
            return WebResponseCustom::getResponseRedirectBack(
                config('constants.ERROR_STATUS'),
                config('constants.UPDATION_FAILED')
            );
        } catch (Throwable $error) {
            report($error);
            return WebResponseCustom::getResponseRedirectBack(
                config('constants.ERROR_STATUS'),
                $error->getMessage()
            );
        }
    }
    /**
     * It will fetch the curved distance between 2 points
     * Google distance matrix API is consumed
     * @author Mirza Abdullah Izhar
     * @version 2.3.0
     */
    public function getDistanceBetweenPoints($destination_lat, $destination_lon, $origin_lat, $origin_lon)
    {
        $origing_address = $origin_lat . ',' . $origin_lon;
        $destination_address = $destination_lat . ',' . $destination_lon;
        /* Rameesha's URL */
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=" . urlencode($origing_address) . "&destinations=" . urlencode($destination_address) . "&mode=driving&key=AIzaSyD_7jrpEkUDW7pxLBm91Z0K-U9Q5gK-10U";
        // dd($url);
        $results = json_decode(file_get_contents($url), true);
        $meters = explode(' ', $results['rows'][0]['elements'][0]['distance']['value']);
        $distanceInMiles = (float)$meters[0] * 0.000621;

        $durationInSeconds = explode(' ', $results['rows'][0]['elements'][0]['duration']['value']);
        $durationInMinutes = round((int)$durationInSeconds[0] / 60);
        return ['distance' => $distanceInMiles, 'duration' => $durationInMinutes];
    }
    /**
     * Fetch seller/store information w.r.t ID
     * If seller/store have distance it will return distance
     * @author Mirza Abdullah Izhar
     * @version 2.1.0
     */
    public static function getSellerInfo(Object $seller_info, array $map_api_result = null)
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
            'roles' => ($seller_info->role_id == 2) ? ['sellers'] : ['child_sellers']
        );
        if (!empty($map_api_result)) {
            $data['distance'] = $map_api_result['distance'];
            $data['duration'] = $map_api_result['duration'];
        }
        return $data;
    }
    /**
     * Listing of all Sellers/Stores within 5 miles
     * @param lat mandatory
     * @param lon mandatory
     * @author Mirza Abdullah Izhar
     * @version 2.1.0
     */
    public function sellers(Request $request)
    {
        try {
            $validate = Validator::make($request->query(), [
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180',
                'city' => 'required|string',
                'page' => 'required|numeric'
            ]);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $users = Cache::remember('sellers' . $request->city . $request->page, now()->addDay(), function () use ($request) {
                return User::getParentAndChildSellers($request->city);
            });
            $pagination = $users->toArray();
            unset($pagination['data']);
            
            if (!$users->isEmpty()) $data = GoogleMap::findDistanceByMakingChunks($request->lat, $request->lon, $users, 25);
         
            if (empty($data)) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_STORES_FOUND'),
                    config('constants.HTTP_OK')
                );
            }

            return JsonResponseCustom::getApiResponseExtention(
                $data,
                config('constants.TRUE_STATUS'),
                '',
                'pagination',
                $pagination,
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
