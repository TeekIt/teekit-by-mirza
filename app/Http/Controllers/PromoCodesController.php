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
use Illuminate\Validation\Rule;
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
                    PromoCode::where('id', '=', $request->promocodes[$i])->forceDelete();
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
    public function allPromoCodes()
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
    public function validatePromoCodes(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'customerId' => 'required|integer|exists:users,id',
            'promoCode' => 'required|string|max:20|exists:promo_codes,promo_code',
            'incrementTotalUsed' => ['required', Rule::in(['yes', 'no'])],
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $validatedData = (object) $validatedData->validated();

        $promoCode = PromoCode::getByPromoCode($validatedData->promoCode, $validatedData->customerId);

        if (PromoCodeHelpers::isExpired($promoCode)) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.EXPIRED_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        }
        /**
         * This condition will only work if the  
         * Promo code is only valid for a specific order#
         */
        if (!empty($promoCode->order_number)) {
            $userTotalOrders = Orders::getByCreatorId($validatedData->customerId)->count();
            $userCurrentOrderNumber = $userTotalOrders + 1;

            if ($userCurrentOrderNumber != $promoCode->order_number) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    'This promo code is only valid for order#' . $promoCode->order_number,
                    config('constants.HTTP_OK')
                );
            }
        }

        if (!empty($promoCode->usage_limit) && $validatedData->incrementTotalUsed === 'yes') {
            return PromoCodeHelpers::incrementTotalUsedAndReturnResponse(
                $promoCode,
                $request
            );
        }

        // $data['promo_code'] = $promoCode;
        // $data['store'] = ($promoCode->store_id) ? (PromoCodeHelpers::getTheSellerBelongsToThisPromoCode($promoCode)) : (object) [];

        return JsonResponseServices::getApiResponse(
            $promoCode,
            config('constants.TRUE_STATUS'),
            config('constants.VALID_PROMOCODE'),
            config('constants.HTTP_OK')
        );
    }
    /**
     * It will fetch a promocode and check all
     * the validation but will not increment the total times
     * a promocode has been used
     */
    public function fetchPromoCodeInfo(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:users,id',
            'promo_code' => 'required|string|max:20|exists:promo_codes,promo_code'
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }
        $promoCodesCount = PromoCode::where('promo_code', '=', $request->promo_code)->count();
        if ($promoCodesCount == 1) {
            $expiryDate = PromoCode::where('promo_code', '=', $request->promo_code)->pluck('expiry_dt')->first();
            $currentDate = date('Y-m-d');
            if ($expiryDate < $currentDate) {
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.EXPIRED_PROMOCODE'),
                    config('constants.HTTP_OK')
                );
            } else {
                $promoCodes = PromoCode::where('promo_code', '=', $request->promo_code)->get();
                if (empty($promoCodes[0]->store_id)) $promoCodes[0]->store_id = NULL;
                //below query will pass required data to our helper functions down below to validate
                $promoCodeData = PromoCode::where('promo_code', $request->promo_code)->first(['id', 'usage_limit', 'store_id', 'discount']);
                /**
                 * This condition will only work if the  
                 * Promo code is only valid for a specific order#
                 */
                if (!empty($promoCodes[0]->order_number)) {
                    $userOrdersCount = Orders::where('customer_id', '=', $request->customer_id)->count();
                    if ($promoCodes[0]->order_number == $userOrdersCount + 1) {
                        $data[0]['promo_code'] = $promoCodes[0];
                        $data[1]['promo_codes_usage_limit'] = ($promoCodes[0]->usage_limit) ? PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->customer_id, $promoCodes[0]->id) : null;
                        $storeData = PromoCodeHelpers::getTheSellerBelongsToThisPromoCode($promoCodeData);
                        $data[2]['store'] = ($storeData) ? ($storeData) : (NULL);
                        return JsonResponseServices::getApiResponse(
                            $data,
                            config('constants.TRUE_STATUS'),
                            config('constants.VALID_PROMOCODE'),
                            config('constants.HTTP_OK')
                        );
                    } else {
                        return JsonResponseServices::getApiResponse(
                            [],
                            config('constants.FALSE_STATUS'),
                            'This promo code is only valid for order#' . $promoCodes[0]->order_number,
                            config('constants.HTTP_OK')
                        );
                    }
                }
                $data[0]['promo_code'] = $promoCodes[0];
                $data[1]['promo_codes_usage_limit'] = ($promoCodes[0]->usage_limit) ? PromoCodesUsageLimit::promoCodeTotalUsedByUser($request->customer_id, $promoCodes[0]->id) : null;
                $storeData = PromoCodeHelpers::getTheSellerBelongsToThisPromoCode($promoCodeData);
                $data[2]['store'] = ($storeData) ? ($storeData) : (NULL);
                return JsonResponseServices::getApiResponse(
                    $data,
                    config('constants.TRUE_STATUS'),
                    config('constants.VALID_PROMOCODE'),
                    config('constants.HTTP_OK')
                );
            }
        } else {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.INVALID_PROMOCODE'),
                config('constants.HTTP_OK')
            );
        }
    }
}
