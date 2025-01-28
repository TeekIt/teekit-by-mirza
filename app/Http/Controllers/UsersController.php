<?php

namespace App\Http\Controllers;

use App\Drivers;
use App\Pages;
use App\Products;
use App\Services\GoogleMapServices;
use App\User;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Http\Request;
use App\Services\JsonResponseServices;
use App\Services\WebResponseServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsersController extends Controller
{
    /**
     * Return's admin settings view
     * @author Muhammad Abdullah Mirza
     */
    public function adminSettings()
    {
        $pageTypes = ['terms', 'help', 'faq', 'slogan', 'favicon', 'logo'];
        $pages = Pages::whereIn('page_type', $pageTypes)->get()->keyBy('page_type');

        $terms_page = $pages->get('terms');
        $help_page = $pages->get('help');
        $faq_page = $pages->get('faq');
        $slogan = $pages->get('slogan');
        $favicon = $pages->get('favicon');
        $logo = $pages->get('logo');

        return view('admin.settings', compact(
            'terms_page',
            'help_page',
            'faq_page',
            'slogan',
            'favicon',
            'logo'
        ));
    }
    /**
     * It will update user details
     * via given id
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    public function updateBuyer(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'fName' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'lName' => 'required|string|max:100|regex:/^[A-Za-z\s]+$/',
            'password' => 'required|string|min:8|max:50',
            'countryCode' => 'required|string|max:4',
            'phone' => 'required|string|max:13',
            'fullAddress' => 'required|string',
            'unitAddress' => 'nullable|string',
            'country' => 'required|string|max:70',
            'state' => 'required|string|max:70',
            'city' => 'required|string|max:70',
            'postcode' => 'nullable|string|max:11',
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $updated = User::updateInfo(
            id: JWTAuth::user()->id,
            name: $request->fName,
            lName: $request->lName,
            // phone: $request->countryCode . $request->phone,
            phone: $request->phone,
            fullAddress: $request->fullAddress,
            unitAddress: $request->unitAddress,
            country: $request->country,
            state: $request->state,
            city: $request->city,
            postcode: $request->postcode,
            lat: $request->lat,
            lon: $request->lon,
            password: $request->password,
        );

        if ($updated) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.UPDATION_SUCCESS'),
                config('constants.HTTP_OK'),
            );
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.FALSE_STATUS'),
            config('constants.UPDATION_FAILED'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * Delete selected users
     * @author Muhammad Abdullah Mirza
     */
    public function adminUsersDel(Request $request)
    {
        User::adminUsersDel($request);

        return response(config('constants.USERS_DELETION_SUCCESS'));
    }
    /**
     * Delete selected drivers
     * @author Muhammad Abdullah Mirza
     */
    public function adminDriversDel(Request $request)
    {
        Drivers::adminDriversDel($request);

        return response(config('constants.DRIVERS_DELETION_SUCCESS'));
    }
    /**
     * @author Muhammad Abdullah Mirza
     */
    public function updateSellerRequiredInfo(Request $request)
    {
        $request->validate([
            'time' => 'required|array',
        ]);

        $time = $request->time;
        foreach ($time as $key => $value) {
            if (!in_array("on", $time[$key])) $time[$key] += ["closed" => null];
        }

        $businessHours['time'] = $time;
        $businessHours['submitted'] = "yes";

        $updated = User::updateInfo(
            auth()->id(),
            hours: $businessHours,
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
     * @author Muhammad Abdullah Mirza
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
            $validatedData = Validator::make($request->all(), [
                'full_address' => 'required|string',
                'unit_address' => 'nullable|string',
                'postcode' => 'required|string',
                'country' => 'required|string',
                'state' => 'required|string',
                'city' => 'required|string',
                'lat' => 'required|numeric|between:-90,90',
                'lon' => 'required|numeric|between:-180,180'
            ]);
            if ($validatedData->fails()) return WebResponseServices::getValidationResponseRedirectBack(
                $validatedData
            );

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
     * @author Muhammad Abdullah Mirza
     */
    public function sellers(Request $request)
    {
        $validatedData = Validator::make($request->query(), [
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'state' => 'required|string',
            // 'page' => 'required|numeric',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $data = Cache::remember('sellers' . $request->state . $request->lat . $request->lon, now()->addDay(), function () use ($request) {
            $sellers = User::getParentAndChildSellersByState($request->state);
            // $pagination = $sellers->toArray();
            // unset($pagination['data']);
            if (!$sellers->isEmpty()) {
                return GoogleMapServices::findDistanceByMakingChunks($request->lat, $request->lon, $sellers, 25);
            }
        });

        if (empty($data)) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_STORES_FOUND'),
                config('constants.HTTP_OK')
            );
        }

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK'),
        );

        // return JsonResponseServices::getApiResponseExtention(
        //     $data,
        //     config('constants.TRUE_STATUS'),
        //     '',
        //     'pagination',
        //     $pagination,
        //     config('constants.HTTP_OK')
        // );
    }
    /**
     * Search products w.r.t Seller/Store 'id' & Product Name
     * @author Muhammad Abdullah Mirza
     * @version 1.4.0
     */
    public function searchSellerProducts($seller_id, $product_name)
    {
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
    }
}
