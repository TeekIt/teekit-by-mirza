<?php

namespace App\Http\Controllers;

use App\Helpers\PromoCodeHelpers;
use App\Models\PromoCodesUsageLimit;
use App\Orders;
use App\PromoCodes;
use App\Role;
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
        if (Gate::allows('superadmin')) {
            //Get stores names for select dropdown
            $stores = Role::find(2)->users;
            $promo_codes = PromoCodes::paginate(10);
            return view('admin.promo_codes', compact('promo_codes', 'stores'));
        } else {
            abort(404);
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
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'expiry_dt' => 'required',
            ]);
            if ($validatedData->fails()) {
                flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            PromoCodes::create([
                "promo_code" => $request->promo_code,
                "discount_type" => $request->discount_type,
                "discount" => $request->discount,
                "order_number" => $request->order_number,
                "usage_limit" => $request->usage_limit,
                "min_amnt_for_discount" => $request->min_amnt_for_discount,
                "max_amnt_for_discount" => $request->max_amnt_for_discount,
                "store_id" => $request->store_id,
                "expiry_dt" => $request->expiry_dt,
            ]);
            flash('Promo code saved successfully.')->success();
            return back();
        } catch (Throwable $error) {
            report($error);
            flash('Failed to save promo code due to some internal error.')->error();
            return back();
        }
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
                    PromoCodes::where('id', '=', $request->promocodes[$i])->delete();
                }
                return response("Promocodes Deleted Successfully");
            }
        } catch (Throwable $error) {
            report($error);
            flash('Failed to delete promo code due to some internal error.')->error();
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
                'min_amnt_for_discount' => 'required|int',
                'max_amnt_for_discount' => 'required|int',
                'expiry_dt' => 'required',
            ]);
            if ($validatedData->fails()) {
                flash('Error in saving the promo code because a required field is missing or invalid data.')->error();
                return Redirect::back()->withInput($request->input());
            }
            $promo_code = PromoCodes::find($id);
            $promo_code->promo_code = $request->promo_code;
            $promo_code->discount_type = $request->discount_type;
            $promo_code->discount = $request->discount;
            $promo_code->order_number = $request->order_number;
            $promo_code->usage_limit = $request->usage_limit;
            $promo_code->min_amnt_for_discount = $request->min_amnt_for_discount;
            $promo_code->max_amnt_for_discount = $request->max_amnt_for_discount;
            $promo_code->expiry_dt = $request->expiry_dt;
            $promo_code->store_id = $request->store_id;
            $promo_code->save();
            flash('Promo code updated successfully.')->success();
            return back();
        } catch (Throwable $error) {
            report($error);
            flash('Failed to update promo code due to some internal error.')->error();
            return back();
        }
    }
    /**
     * function will return all promocodes from table
     */
    public function allPromocodes()
    {
        try {
            $promocodes = PromoCodes::all();
            if ($promocodes->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.NO_RECORD')
                ], 200);
            } else {
                return response()->json([
                    'data' => $promocodes,
                    'status' => true,
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
                'user_id' => 'required|int',
                'promo_code' => 'required|string|max:20'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $promocodes_count = PromoCodes::where('promo_code', '=', $request->promo_code)->count();
            if ($promocodes_count == 1) {
                $expiry_dt = PromoCodes::where('promo_code', '=', $request->promo_code)->pluck('expiry_dt')->first();
                $current_date = date('Y-m-d');
                if ($expiry_dt < $current_date) {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' =>  config('constants.EXPIRED_PROMOCODE')
                    ], 200);
                } else {
                    $promo_codes = PromoCodes::where('promo_code', '=', $request->promo_code)->get();
                    if (empty($promo_codes[0]->store_id)) $promo_codes[0]->store_id = NULL;
                    //below query will pass required data to our helper functions down below to validate
                    $promo_code_data = PromoCodes::where('promo_code', $request->promo_code)->first(['id', 'usage_limit', 'store_id', 'discount']);
                    /**
                     * This condition will only work if the  
                     * Promo code is only valid for a specific order#
                     */
                    if (!empty($promo_codes[0]->order_number)) {
                        $user_orders_count = Orders::where('user_id', '=', $request->user_id)->count();
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
                'user_id' => 'required|int',
                'promo_code' => 'required|string|max:20'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $promocodes_count = PromoCodes::where('promo_code', '=', $request->promo_code)->count();
            if ($promocodes_count == 1) {
                $expiry_dt = PromoCodes::where('promo_code', '=', $request->promo_code)->pluck('expiry_dt')->first();
                $current_date = date('Y-m-d');
                if ($expiry_dt < $current_date) {
                    return response()->json([
                        'data' => [],
                        'status' => false,
                        'message' =>  config('constants.EXPIRED_PROMOCODE')
                    ], 200);
                } else {
                    $promo_codes = PromoCodes::where('promo_code', '=', $request->promo_code)->get();
                    if (empty($promo_codes[0]->store_id)) $promo_codes[0]->store_id = NULL;
                    //below query will pass required data to our helper functions down below to validate
                    $promo_code_data = PromoCodes::where('promo_code', $request->promo_code)->first(['id', 'usage_limit', 'store_id', 'discount']);
                    /**
                     * This condition will only work if the  
                     * Promo code is only valid for a specific order#
                     */
                    if (!empty($promo_codes[0]->order_number)) {
                        $user_orders_count = Orders::where('user_id', '=', $request->user_id)->count();
                        if ($promo_codes[0]->order_number == $user_orders_count + 1) {
                            $data[0]['promo_code'] = $promo_codes[0];
                            $data[1]['promo_codes_usage_limit'] = ($promo_codes[0]->usage_limit) ? PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->user_id, $promo_codes[0]->id) : null;
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
                    $data[1]['promo_codes_usage_limit'] = ($promo_codes[0]->usage_limit) ? PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->user_id, $promo_codes[0]->id) : null;
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
