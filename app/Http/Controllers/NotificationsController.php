<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\notifications;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class NotificationsController extends Controller
{
    /**
     * it will fetch all the notifications
     * @version 1.0.0
     */
    public function getNotifications()
    {
        $notifications = notifications::query()->where('user_id', '=', Auth::id())->get();
        if ($notifications->count() <= 0) {
            return response()->json([
                'data' => [],
                'status' => false,
                'message' => 'No New Notifications'
            ], 200);
        } else {
            return response()->json([
                'data' => $notifications,
                'status' => true,
                'message' => 'User Notifications'
            ], 200);
        }
    }
    /**
     * it will delete the notification via 
     * given id
     * @version 1.0.0
     */
    public function deleteNotification($notification_id)
    {
        try {
            $notification = notifications::find($notification_id);
            if (!empty($notification)) {
                $notification->delete();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.ITEM_DELETED'),
                ], 200);
            } else {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => config('constants.NO_RECORD')
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
     * Returns notification form view
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function notificationHome(Request $request)
    {
        if (Gate::allows('superadmin')) {
            return view('admin.notification');
        } else {
            abort(404);
        }
    }
    /**
     * It will send notifications
     * @author Mirza Abdullah Izhar
     * @version 1.1.0
     */
    public function notificationSend(Request $request)
    {
        try {
            if (Gate::allows('superadmin')) {
                $validatedData = notifications::validator($request);
                if ($validatedData->fails()) {
                    flash('Error in sending notification because a required field is missing or invalid data.')->error();
                    return Redirect::back()->withInput($request->input());
                }
                $firebaseToken = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
                $data = [
                    "registration_ids" => $firebaseToken,
                    "notification" => [
                        "title" => $request->title,
                        "body" => $request->body,
                    ],
                    "priority" => "high"
                ];
                $dataString = json_encode($data);
                $headers = [
                    'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
                    'Content-Type: application/json',
                ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                curl_exec($ch);
                curl_close($ch);
                return back()->with('success', 'Notification send successfully.');
            } else {
                abort(404);
            }
        } catch (Throwable $error) {
            report($error);
            return back()->with('error', 'Failed to send the notification due to some internal error.');
        }
    }
    /**
     * Test send notifications firebase API
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function notificationSendTest(Request $request)
    {
        try {
            $validatedData = notifications::validator($request);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => $validatedData->errors(),
                    'status' => true,
                    'message' => ""
                ], 422);
            }

            $firebaseToken = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => $request->title,
                    "body" => $request->body,
                ],
                "priority" => "high"
            ];

            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            curl_exec($ch);
            $response = $ch;
            curl_close($ch);

            print_r($response);
            exit;
            return response()->json([
                'data' => $response,
                'status' => true,
                'message' => ""
            ], 200);
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
     * It will save/update device token of every user
     * @author Mirza Abdullah Izhar
     * @version 1.0.0
     */
    public function saveToken(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'device_id' => 'required|string',
                'device_token' => 'required|string'
            ]);
            if ($validatedData->fails()) {
                return response()->json([
                    'data' => [],
                    'status' => false,
                    'message' => $validatedData->errors()
                ], 422);
            }
            $device_token = new DeviceToken();
            $count = $device_token::select()->where('device_id', $request->device_id)->count();
            if ($count == 0) {
                $device_token->user_id = $request->user_id;
                $device_token->device_id = $request->device_id;
                $device_token->device_token = $request->device_token;
                $device_token->save();
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.DATA_INSERTION_SUCCESS')
                ], 200);
            } else {
                $device_token::where('device_id', $request->device_id)
                    ->update(['user_id' => $request->user_id, 'device_token' => $request->device_token]);
                return response()->json([
                    'data' => [],
                    'status' => true,
                    'message' => config('constants.DATA_UPDATED_SUCCESS')
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