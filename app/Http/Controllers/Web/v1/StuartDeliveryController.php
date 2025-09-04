<?php

namespace App\Http\Controllers\Web\v1;

use App\Models\StuartDelivery;
use App\Orders;
use App\Services\CompanyStandardsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Throwable;
use App\Services\StuartDeliveryServices;
use App\Services\WebResponseServices;
use App\Http\Controllers\Controller;

class StuartDeliveryController extends Controller
{
    /**
     * Creates a stuart delivery job
     * @author Muhammad Abdullah Mirza
     * @version 1.0.0
     */
    // public function stuartJobCreationForWeb(Request $request)
    // {
    //     try {
    //         $order_details = Orders::with('store')->where('id', '=', $request->order_id)->first();
    //         $transport_type = Orders::fetchTransportType($request->order_id);

    //         $job = [
    //             'job' => [
    //                 'pickup_at' => CompanyStandardsServices::getStandardPickUpTime(),
    //                 'assignment_code' => $request->order_id,
    //                 'pickups' => [
    //                     [
    //                         'address' => $order_details->store->address_1,
    //                         'comment' => 'Please come at the pickup point as early as possible. Also call us to confirm the order package type.',
    //                         'contact' => [
    //                             'firstname' => $order_details->store->name,
    //                             'phone' => $order_details->store->business_phone,
    //                             'email' => $order_details->store->email,
    //                             'company' => $order_details->store->business_name
    //                         ]
    //                     ]
    //                 ],
    //                 'dropoffs' => [
    //                     [
    //                         'package_type' => 'medium',
    //                         'package_description' => 'Package purchased from Teek it.',
    //                         'transport_type' => $transport_type,
    //                         'client_reference' => ($request->custom_order_id) ? $request->custom_order_id : $request->order_id,
    //                         'address' => $order_details->address . ' House#' . $order_details->house_no,
    //                         'comment' => 'Please try to call the customer before reaching the destination.',
    //                         'contact' => [
    //                             'firstname' => $order_details->receiver_name,
    //                             'phone' => $order_details->phone_number,
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ];

    //         $data = StuartDeliveryServices::createJob($job);
    //         if ($data && !isset($data['error'])) {
    //             $data = StuartDelivery::create([
    //                 'order_id' => $request->order_id,
    //                 'job_id' => $data['id']
    //             ]);
    //             Orders::where('id', $request->order_id)->update([
    //                 'order_status' => 'stuartDelivery'
    //             ]);
    //             WebResponseServices::getWebResponse(config('constants.TRUE_STATUS'), config('constants.STUART_DELIVERY_SUCCESS'));

    //             return Redirect::back();
    //         } else {
    //             $message = $data['message'];
    //             if ($data['error'] == 'JOB_DISTANCE_NOT_ALLOWED') $message = $message . " " . $transport_type;
    //             WebResponseServices::getWebResponse(config('constants.FALSE_STATUS'), $message);

    //             return Redirect::back();
    //         }
    //     } catch (Throwable $error) {
    //         report($error);
    //         WebResponseServices::getWebResponse(config('constants.FALSE_STATUS'), $data['message']);

    //         return Redirect::back();
    //     }
    // }
}
