<?php

namespace App\Http\Controllers;

use App\Services\JsonResponseServices;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Throwable;

class WalletController extends Controller
{
    /**
     * @version 1.0.0
     */
    // public function update(Request $request)
    // { 
    //     try {
    //         $validate = Validator::make($request->all(), [
    //             'user_id' => 'required|integer',
    //             'updated_amount' => 'required|numeric'
    //         ]);
    //         if ($validate->fails()) {
    //             return JsonResponseServices::getApiResponse(
    //                 [],
    //                 config('constants.FALSE_STATUS'),
    //                 $validate->errors(),
    //                 config('constants.HTTP_UNPROCESSABLE_REQUEST')
    //             );
    //         }
    //         $updated = User::updateWallet($request->user_id, $request->updated_amount);
    //         return JsonResponseServices::getApiResponse(
    //             [],
    //             ($updated) ? config('constants.TRUE_STATUS') : config('constants.FALSE_STATUS'),
    //             ($updated) ? config('constants.UPDATION_SUCCESS') : config('constants.UPDATION_FAILED'),
    //             config('constants.HTTP_OK')
    //         );
    //     } catch (Throwable $error) {
    //         report($error);
    //         return JsonResponseServices::getApiResponse(
    //             [],
    //             config('constants.FALSE_STATUS'),
    //             $error,
    //             config('constants.HTTP_SERVER_ERROR')
    //         );
    //     }
    // }
}
