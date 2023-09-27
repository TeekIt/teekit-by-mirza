<?php

namespace App\Http\Controllers;

use App\Models\ReferralCodeRelation;
use App\Orders;
use App\Services\JsonResponseCustom;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ReferralCodeRelationController extends Controller
{
    /**
     * The amount which will be rewarded after applying a valid referral code
     */
    private $amount = 10.00;
    /**
     * @version 1.1.0
     */
    public function validateReferral(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'user_id' => 'required|int',
                'referral_code' => 'required|uuid'
            ]);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $is_verified = User::verifyReferralCode($request->user_id, $request->referral_code);
            if (!$is_verified) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.INVALID_REFERRAL'),
                    config('constants.HTTP_OK')
                );
            }

            $using_referral_first_time = ReferralCodeRelation::usingReferalFirstTime($request->user_id);
            if (!$using_referral_first_time) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.REFERRAL_CAN_BE_USED_ONCE'),
                    config('constants.HTTP_OK')
                );
            }

            if (Orders::checkTotalOrders($request->user_id) === 0) {
                ReferralCodeRelation::insertReferralRelation($is_verified->id, $request->user_id);
                User::addIntoWallet($request->user_id, $this->amount);
                return JsonResponseCustom::getApiResponse(
                    ['discount' => $this->amount],
                    config('constants.TRUE_STATUS'),
                    config('constants.VALID_REFERRAL'),
                    config('constants.HTTP_OK')
                );
            } else {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.REFERRALS_ARE_ONLY_FOR_FIRST_ORDER'),
                    config('constants.HTTP_OK')
                );
            }
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
    /**
     * @version 1.0.0
     */
    public function insertReferrals()
    {
        try {
            foreach (User::getBuyers() as $buyer) {
                $buyer_obj = User::find($buyer->id);
                $buyer_obj->referral_code = Str::uuid();
                $buyer_obj->save();
            }
            return JsonResponseCustom::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.INSERTION_SUCCESS'),
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
    /**
     * @version 1.0.0
     */
    public function fetchReferralRelationDetails(int $referral_relation_id)
    {
        try {
            $referral_reltaion_details = ReferralCodeRelation::getReferralRelationDetails($referral_relation_id);
            if ($referral_reltaion_details->isEmpty()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    config('constants.NO_RECORD'),
                    config('constants.HTTP_OK')
                );
            }
            $data = User::getUserInfo($referral_reltaion_details[0]->referredByUser->id);
            $data['referral_useable'] = $referral_reltaion_details[0]->referral_useable;
            $data['referral_relation_id'] = $referral_reltaion_details[0]->id;
            return JsonResponseCustom::getApiResponse(
                $data,
                config('constants.TRUE_STATUS'),
                '',
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
    /**
     * @version 1.0.0
     */
    public function updateReferralStatus(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'referral_relation_id' => 'required|integer',
                'referral_useable' => 'required|integer'
            ]);
            if ($validate->fails()) {
                return JsonResponseCustom::getApiResponse(
                    [],
                    config('constants.FALSE_STATUS'),
                    $validate->errors(),
                    config('constants.HTTP_UNPROCESSABLE_REQUEST')
                );
            }
            $updated = ReferralCodeRelation::updateReferralRelationStatus($request->referral_relation_id, $request->referral_useable);
            if ($updated == 1 && $request->referral_useable == 0) {
                // Update wallet of the referred by user of referral relationship
                $referral_reltaion_details = ReferralCodeRelation::getReferralRelationDetails($request->referral_relation_id);
                User::addIntoWallet($referral_reltaion_details[0]->referredByUser->id, $this->amount);
            }
            return JsonResponseCustom::getApiResponse(
                [],
                ($updated) ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
                ($updated) ? config('constants.UPDATION_SUCCESS') : config('constants.UPDATION_FAILED'),
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
