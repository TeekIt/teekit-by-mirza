<?php

namespace App\Http\Controllers;

use App\Helpers\PromoCodeHelpers;
use App\Models\PromoCodesUsageLimit;
use App\Orders;
use App\Models\PromoCode;
use App\Rules\Seller\IsParentOrChildSellerId;
use App\Services\JsonResponseServices;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PromoCodesController extends Controller
{
    /**
     * Returns promo codes form & list view
     * @version 1.0.0
     */
    public function promocodesHome()
    {
        /* Get stores names for select dropdown */
        $stores = User::getParentAndChildSellersList(['id', 'business_name']);
        $promoCodes = PromoCode::getAll('desc');

        return view('admin.promo_codes', compact('promoCodes', 'stores'));
    }
    /**
     * Deletes the specific promo code via ajax call
     * @version 1.0.0
     */
    public function promoCodesDel(Request $request)
    {
        try {
            if (Gate::allows('superadmin')) {
                for ($i = 0; $i < count($request->promocodes); $i++) {
                    PromoCode::where('id', '=', $request->promocodes[$i])->delete();
                }

                return response("Promocodes Deleted Successfully");
            }
        } catch (Throwable $error) {
            report($error);
            session()->flash('error', $error->getMessage());

            return back();
        }
    }
    /**
     * Adds promo code into the database
     * @version 1.0.0
     */
    public function promocodesAdd(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'promo_code' => 'required|string|unique:promo_codes|max:20',
                'discount_type' => 'required',
                'discount' => 'required|int',
                'order_number' => 'nullable|int',
                'usage_limit' => 'nullable|int',
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'store_id' => ['nullable', new IsParentOrChildSellerId],
                'free_delivery' => 'nullable|string',
                'expiry_dt' => 'required',
            ]);
            if ($validatedData->fails()) {
                session()->flash('error', $validatedData->errors());

                return Redirect::back()->withInput($request->input());
            }

            $validatedData = $validatedData->validated();

            PromoCode::addOrUpdate($validatedData);

            session()->flash('success', 'Promo code saved successfully.');

            return back();
        } catch (Throwable $error) {
            report($error);
            session()->flash('error', $error->getMessage());

            return back();
        }
    }
    /**
     * Updates the specific promo code via popup modal
     * @version 1.0.0
     */
    public function promoCodesUpdate(Request $request, $id)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'promo_code' => 'required|string|max:20',
                'discount_type' => 'required',
                'discount' => 'required|int',
                'order_number' => 'nullable|int',
                'usage_limit' => 'nullable|int',
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'store_id' => ['nullable', new IsParentOrChildSellerId],
                'free_delivery' => 'nullable|string',
                'expiry_dt' => 'required|date',
            ]);
            if ($validatedData->fails()) {
                session()->flash('error', $validatedData->errors());

                return Redirect::back()->withInput($request->input());
            }

            $validatedData = $validatedData->validated();

            PromoCode::addOrUpdate($validatedData, $id);

            session()->flash('success', 'Promo code updated successfully.');

            return back();
        } catch (Throwable $error) {
            report($error);
            session()->flash('error', $error->getMessage());

            return back();
        }
    }
    /**
     * function will return all promocodes from table
     */
    public function allPromocodes()
    {
        $promocodes = PromoCode::all();

        $dataIsEmpty = $promocodes->isEmpty();

        return JsonResponseServices::getApiResponse(
            ($dataIsEmpty) ? [] : $promocodes,
            ($dataIsEmpty) ? config('constants.FALSE_STATUS') : config('constants.TRUE_STATUS'),
            ($dataIsEmpty) ? config('constants.NO_RECORD') : '',
            config('constants.HTTP_OK')
        );
    }
    /**
     * Validates either the given promo code is correct or not
     * It also checks that either the user is submitting this
     * Promo code for the right order number or not 
     * Further it will increment the usage limit
     * @version 1.2.0
     */
    public function promocodesValidate(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'customer_id' => 'required|int',
                'promo_code' => 'required|string|max:20'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $promocodes_count = PromoCode::where('promo_code', '=', $request->promo_code)->count();
            if ($promocodes_count == 1) {
                $expiry_dt = PromoCode::where('promo_code', '=', $request->promo_code)->pluck('expiry_dt')->first();
                $current_date = date('Y-m-d');
                if ($expiry_dt < $current_date) {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' =>  config('constants.EXPIRED_PROMOCODE')
                    ], 200);
                } else {
                    $promo_codes = PromoCode::where('promo_code', '=', $request->promo_code)->get();
                    if (empty($promo_codes[0]->store_id)) $promo_codes[0]->store_id = NULL;
                    //below query will pass required data to our helper functions down below to validate
                    $promo_code_data = PromoCode::where('promo_code', $request->promo_code)->first(['id', 'usage_limit', 'store_id', 'discount']);
                    /**
                     * This condition will only work if the  
                     * Promo code is only valid for a specific order#
                     */
                    if (!empty($promo_codes[0]->order_number)) {
                        $user_orders_count = Orders::where('customer_id', '=', $request->customer_id)->count();
                        if ($promo_codes[0]->order_number == $user_orders_count + 1) {
                            if (!empty($promo_code_data->usage_limit)) return PromoCodeHelpers::checkUsageLimit($promo_codes, $promo_code_data, $request);
                        } else {
                            return response()->json([
                                'data' => [],
                                'status' => false,
                                'message' => 'This promo code is only valid for order#' . $promo_codes[0]->order_number
                            ], 200);
                        }
                    }
                    /**
                     * If the Promo code does not belongs to a specific order# 
                     * Still we have to validate it's usage limit 
                     */
                    if (!empty($promo_code_data->usage_limit))
                        return PromoCodeHelpers::checkUsageLimit($promo_codes, $promo_code_data, $request);

                    $data[0]['promo_code'] = $promo_codes[0];
                    $store_data = PromoCodeHelpers::ifPromoCodeBelongsToStore($promo_code_data);
                    $data[1]['store'] = ($store_data) ? ($store_data) : (NULL);
                    return response()->json([
                        'data' => $data,
                        'status' => true,
                        'message' => config('constants.VALID_PROMOCODE')
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.INVALID_PROMOCODE')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
    /**
     * function will fetch a promocode and check all
     * the validation but will not increment the total times
     * a promocode has been used
     */
    public function fetchPromocodeInfo(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'customer_id' => 'required|int',
                'promo_code' => 'required|string|max:20'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $promocodes_count = PromoCode::where('promo_code', '=', $request->promo_code)->count();
            if ($promocodes_count == 1) {
                $expiry_dt = PromoCode::where('promo_code', '=', $request->promo_code)->pluck('expiry_dt')->first();
                $current_date = date('Y-m-d');
                if ($expiry_dt < $current_date) {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' =>  config('constants.EXPIRED_PROMOCODE')
                    ], 200);
                } else {
                    $promo_codes = PromoCode::where('promo_code', '=', $request->promo_code)->get();
                    if (empty($promo_codes[0]->store_id)) $promo_codes[0]->store_id = NULL;
                    //below query will pass required data to our helper functions down below to validate
                    $promo_code_data = PromoCode::where('promo_code', $request->promo_code)->first(['id', 'usage_limit', 'store_id', 'discount']);
                    /**
                     * This condition will only work if the  
                     * Promo code is only valid for a specific order#
                     */
                    if (!empty($promo_codes[0]->order_number)) {
                        $user_orders_count = Orders::where('customer_id', '=', $request->customer_id)->count();
                        if ($promo_codes[0]->order_number == $user_orders_count + 1) {
                            $data[0]['promo_code'] = $promo_codes[0];
                            $data[1]['promo_codes_usage_limit'] = ($promo_codes[0]->usage_limit) ? PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->customer_id, $promo_codes[0]->id) : null;
                            $store_data = PromoCodeHelpers::ifPromoCodeBelongsToStore($promo_code_data);
                            $data[2]['store'] = ($store_data) ? ($store_data) : (NULL);
                            return response()->json([
                                'data' => $data,
                                'status' => true,
                                'message' => config('constants.VALID_PROMOCODE')
                            ], 200);
                        } else {
                            return response()->json([
                                'data' => [],
                                'status' => false,
                                'message' => 'This promo code is only valid for order#' . $promo_codes[0]->order_number
                            ], 200);
                        }
                    }
                    $data[0]['promo_code'] = $promo_codes[0];
                    $data[1]['promo_codes_usage_limit'] = ($promo_codes[0]->usage_limit) ? PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->customer_id, $promo_codes[0]->id) : null;
                    $store_data = PromoCodeHelpers::ifPromoCodeBelongsToStore($promo_code_data);
                    $data[2]['store'] = ($store_data) ? ($store_data) : (NULL);
                    return response()->json([
                        'data' => $data,
                        'status' => true,
                        'message' => config('constants.VALID_PROMOCODE')
                    ], 200);
                }
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.INVALID_PROMOCODE')
                ], 200);
            }
        } catch (Throwable $error) {
            report($error);
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => $error
            ], 500);
        }
    }
}
