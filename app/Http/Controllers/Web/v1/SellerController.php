<?php

namespace App\Http\Controllers\Web\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WebResponseServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SellerController extends Controller
{
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
            if (! in_array('on', $time[$key])) {
                $time[$key] += ['closed' => null];
            }
        }

        $businessHours['time'] = $time;
        $businessHours['submitted'] = 'yes';

        $updated = User::updateInfo(
            User::getAuthUser()->id,
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
}
