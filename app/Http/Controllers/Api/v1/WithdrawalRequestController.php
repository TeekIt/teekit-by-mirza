<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JsonResponseServices;
use App\Models\WithdrawalRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequestController extends Controller
{
    /**
     *It will send withdrawl request
     *
     * @version 1.0.0
     */
    public function sendRequest(Request $request)
    {
        $user = User::getAuthUser();

        WithdrawalRequests::create([
            'user_id' => $user->id,
            'amount' => $user->wallet,
            'bank_detail' => $request->bank_detail,
            'status' => 'Pending',
        ]);

        $user->update(['wallet' => 0.0]);

        return JsonResponseServices::getApiResponse(
            WithdrawalRequests::getWithdrawalResquests(Auth::id()),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }

    /**
     *Fetch withdrawl requests of logged in user
     *
     * @version 1.0.0
     */
    public function getRequests()
    {
        return JsonResponseServices::getApiResponse(
            WithdrawalRequests::getWithdrawalResquests(Auth::id()),
            config('constants.TRUE_STATUS'),
            '',
            config('constants.HTTP_OK')
        );
    }
}
