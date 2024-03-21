<?php

namespace App\Http\Controllers;

use App\Models\StuartDelivery;
use App\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Throwable;
use App\Services\JsonResponseServices;
use Illuminate\Support\Carbon;

class StuartDeliveryController extends Controller
{
    /**
     * It will get a fresh token for hitting Stuart delivery API
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function stuartSandboxAccessToken()
    {
        $stuart_auth = Http::asForm()->post('' . config("constants.STUART_SANDBOX_TOKEN_URL") . '', [
            'client_id' => config('constants.STUART_SANDBOX_CLIENT_ID'),
            'client_secret' => config('constants.STUART_SANDBOX_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuart_auth = $stuart_auth->json();
        return $stuart_auth['access_token'];
    }
    /**
     * @version 1.0.0
     */
    public function stuartProductionAccessToken()
    {
        $stuart_auth = Http::asForm()->post('' . config("constants.STUART_PRODUCTION_TOKEN_URL") . '', [
            'client_id' => config('app.STUART_PRODUCTION_CLIENT_ID'),
            'client_secret' => config('app.STUART_PRODUCTION_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => 'api'
        ]);
        $stuart_auth = $stuart_auth->json();
        return $stuart_auth['access_token'];
    }
    /**
     * @version 1.0.0
     */
    public function stuartSandboxJobCreation(string $access_token, array $job)
    {
        $response = Http::withToken($access_token)->post('' . config("constants.STUART_SANDBOX_JOBS_URL") . '', $job);
        return $response->json();
    }
    /**
     * @version 1.0.0
     */
    public function stuartProductionJobCreation(string $access_token, array $job)
    {
        $response = Http::withToken($access_token)->post('' . config("constants.STUART_PRODUCTION_JOBS_URL") . '', $job);
        return $response->json();
    }
    /**
     * @version 1.0.0
     */
    public function stuartSandboxJobStatus(string $access_token, array $job_id)
    {
        $response = Http::withToken($access_token)->patch('' . config("constants.STUART_SANDBOX_JOBS_URL") . '/' . $job_id);
        return $response->json();
    }
    /**
     * @version 1.0.0
     */
    public function stuartProductionJobStatus(string $access_token, array $job_id)
    {
        $response = Http::withToken($access_token)->patch('' . config("constants.STUART_PRODUCTION_JOBS_URL") . '/' . $job_id);
        return $response->json();
    }
    /**
     * Creates a stuart delivery job
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function stuartJobCreation(Request $request)
    {
        try {
            $order_details = Orders::with('store')->where('id', '=', $request->order_id)->first();
            $transport_type = Orders::fetchTransportType($request->order_id);
            $access_token = (url('/') == 'https://app.teekit.co.uk') ? $this->stuartProductionAccessToken() : $this->stuartSandboxAccessToken();

            $job = [
                'job' => [
                    'pickup_at' => Carbon::now()->addMinutes(10),
                    'assignment_code' => $request->order_id,
                    'pickups' => [
                        [
                            'address' => $order_details->store->address_1,
                            'comment' => 'Please come at the pickup point as early as possible. Also call us to confirm the order package type.',
                            'contact' => [
                                'firstname' => $order_details->store->name,
                                // 'lastname' => 'null',
                                'phone' => $order_details->store->business_phone,
                                'email' => $order_details->store->email,
                                'company' => $order_details->store->business_name
                            ]
                        ]
                    ],
                    'dropoffs' => [
                        [
                            'package_type' => 'medium',
                            'package_description' => 'Package purchased from Teek it.',
                            'transport_type' => $transport_type,
                            'client_reference' => ($request->custom_order_id) ? $request->custom_order_id : $request->order_id,
                            'address' => $order_details->address . ' House#' . $order_details->house_no,
                            'comment' => 'Please try to call the customer before reaching the destination.',
                            // 'end_customer_time_window_start' => '2021-12-12T11:00:00.000+02:00',
                            // 'end_customer_time_window_end' => '2021-12-12T13:00:00.000+02:00',
                            'contact' => [
                                'firstname' => $order_details->receiver_name,
                                // 'lastname' => 'null',
                                'phone' => $order_details->phone_number,
                                // 'email' => 'client3@email.com',
                                // 'company' => 'Sample Company Inc.'
                            ]
                        ]
                    ]
                ]
            ];

            $data = (url('/') == 'https://app.teekit.co.uk') ? $this->stuartProductionJobCreation($access_token, $job) : $this->stuartSandboxJobCreation($access_token, $job);
            if ($data && !isset($data['error'])) {
                $data = StuartDelivery::create([
                    'order_id' => $request->order_id,
                    'job_id' => $data['id']
                ]);
                Orders::where('id', $request->order_id)->update([
                    'order_status' => 'stuartDelivery'
                ]);
                JsonResponseServices::getWebResponse(config('constants.TRUE_STATUS'), config('constants.STUART_DELIVERY_SUCCESS'));
                return Redirect::back();
            } else {
                $message = $data['message'];
                if ($data['error'] == 'JOB_DISTANCE_NOT_ALLOWED') $message = $message . " " . $transport_type;
                JsonResponseServices::getWebResponse(config('constants.FALSE_STATUS'), $message);
                return Redirect::back();
            }
        } catch (Throwable $error) {
            report($error);
            JsonResponseServices::getWebResponse(config('constants.FALSE_STATUS'), $data['message']);
            return Redirect::back();
        }
    }
    /**
     * It will check the current status of a Stuart job
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function stuartJobStatus(Request $request)
    {
        try {
            $stuart_delivery = StuartDelivery::select('job_id')->where('order_id', $request->order_id)->first();
            $access_token = (url('/') == 'https://app.teekit.co.uk') ? $this->stuartProductionAccessToken() : $this->stuartSandboxAccessToken();
            $data = (url('/') == 'https://app.teekit.co.uk') ? $this->stuartProductionJobStatus($access_token, $stuart_delivery->job_id) : $this->stuartSandboxJobStatus($access_token, $stuart_delivery->job_id);

            if ($data['status'] == 'finished') {
                Orders::updateOrderStatus($request->order_id, 'complete');
                return JsonResponseServices::getApiResponse(
                    [],
                    config('constants.TRUE_STATUS'),
                    config('constants.COMPLETED'),
                    config('constants.HTTP_OK')
                );
            }
            return JsonResponseServices::getApiResponse(
                $data,
                config('constants.TRUE_STATUS'),
                '',
                config('constants.HTTP_OK')
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR')
            );
        }
    }
}
