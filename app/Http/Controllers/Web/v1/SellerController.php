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
                'lon' => 'required|numeric|between:-180,180',
            ]);
            if ($validatedData->fails()) {
                return WebResponseServices::getValidationResponseRedirectBack(
                    $validatedData
                );
            }

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
}
