<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ReferralCodeRelation;
use App\Models\Orders;
use App\Services\JsonResponseServices;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReferralCodeRelationController extends Controller
{
    /**
     * The amount which will be rewarded after applying a valid referral code
     */
    private $rewardAmount = 10.00;

    /**
     * @author Muhammad Abdullah Mirza
     */
    public function validateReferral(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'user_id' => 'required|int',
            'referral_code' => 'required|uuid',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $is_verified = User::verifyReferralCode($request->user_id, $request->referral_code);
        if (! $is_verified) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.INVALID_REFERRAL'),
                config('constants.HTTP_OK')
            );
        }

        $using_referral_first_time = ReferralCodeRelation::usingReferalFirstTime($request->user_id);
        if (! $using_referral_first_time) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.REFERRAL_CAN_BE_USED_ONCE'),
                config('constants.HTTP_OK')
            );
        }

        if (Orders::checkTotalOrders($request->user_id) === 0) {
            ReferralCodeRelation::insertReferralRelation($is_verified->id, $request->user_id);
            User::addIntoWallet($request->user_id, $this->rewardAmount);

            return JsonResponseServices::getApiResponse(
                ['discount' => $this->rewardAmount],
                config('constants.TRUE_STATUS'),
                config('constants.VALID_REFERRAL'),
                config('constants.HTTP_OK')
            );
        } else {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.REFERRALS_ARE_ONLY_FOR_FIRST_ORDER'),
                config('constants.HTTP_OK')
            );
        }
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public function insertReferrals()
    {
        foreach (User::getAllCustomers() as $customer) {
            $buyer = User::find($customer->id);
            $buyer->referral_code = Str::uuid();
            $buyer->save();
        }

        return JsonResponseServices::getApiResponse(
            [],
            config('constants.TRUE_STATUS'),
            config('constants.DATA_INSERTION_SUCCESS'),
            config('constants.HTTP_OK')
        );
    }

    /**
     * @author Muhammad Abdullah Mirza
     */
    public function fetchReferralRelationDetails(int $referral_relation_id)
    {
        $referral_reltaion_details = ReferralCodeRelation::getReferralRelationDetails($referral_relation_id);
        if ($referral_reltaion_details->isEmpty()) {
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                config('constants.NO_RECORD'),
                config('constants.HTTP_OK')
            );
        }

        $data = User::getUserInfo($referral_reltaion_details[0]->referredByUser->id);
        $data['referral_useable'] = $referral_reltaion_details[0]->referral_useable;
        $data['referral_relation_id'] = $referral_reltaion_details[0]->id;

        return JsonResponseServices::getApiResponse(
            $data,
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     * @version 1.0.0
     */
    public function updateReferralStatus(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'referral_relation_id' => 'required|integer',
            'referral_useable' => 'required|integer',
        ]);
        if ($validatedData->fails()) {
            return JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
        }

        $updated = ReferralCodeRelation::updateReferralRelationStatus($request->referral_relation_id, $request->referral_useable);
        if ($updated == 1 && $request->referral_useable == 0) {
            /* Update wallet of the referred_by_user of referral relationship */
            $referral_reltaion_details = ReferralCodeRelation::getReferralRelationDetails($request->referral_relation_id);
            User::addIntoWallet($referral_reltaion_details[0]->referredByUser->id, $this->rewardAmount);
        }

        return JsonResponseServices::getApiResponse(
            [],
            ($updated) ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
            ($updated) ? config('constants.UPDATION_SUCCESS') : config('constants.UPDATION_FAILED'),
            config('constants.HTTP_OK')
        );
    }
}
