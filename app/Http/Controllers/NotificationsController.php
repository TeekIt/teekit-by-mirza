<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use Illuminate\Http\Request;
use App\Models\Notification;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Google\Client;
use Throwable;

class NotificationsController extends Controller
{
    /**
     * it will fetch all the notifications
     * @version 1.0.0
     */
    public function getNotifications()
    {
        $notifications = Notification::where('user_id', '=', Auth::id())->get();
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
            $notification = Notification::find($notification_id);
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
     * @author Muhammad Abdullah Mirza
     * @version 1.0.0
     */
    public function notificationHome()
    {
        return view('admin.notification');
    }

    public function getAccessToken($serviceAccountPath)
    {
        $client = new Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $token = $client->fetchAccessTokenWithAssertion();
        // dd($token);
        return $token['access_token'];
    }

    public function sendMessage($accessToken, $projectId, $message)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    public function notificationSend(Request $request)
    {
        try {
            // Path to your service account JSON key file
            $serviceAccountPath = storage_path('app/googleFcmServices/teek-it-965a8-1ec96a1aa676.json');
            
            // Your Firebase project ID
            $projectId = 'teek-it-965a8';
            
            $firebaseTokens = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
        
            if(empty($firebaseTokens)){
                return response()->json(['error' => 'No valid device tokens available.'], 400);
            }
        
        
            $message = [
                'token' => $firebaseTokens[0], // Sending to one token at a time            
                    'notification' => [
                        'title' => 'Hello',
                        'body' => 'World',
                    ],
                    // 'priority' => 'high',
            
            ];
        
            $accessToken = $this->getAccessToken($serviceAccountPath);
            $response = $this->sendMessage($accessToken, $projectId, $message);

            // Check if the response contains an error
            if (isset($response['error'])) {
                if ($response['error']['status'] === 'NOT_FOUND' && $response['error']['details'][0]['errorCode'] === 'UNREGISTERED') {
                    return response()->json(['error' => 'The device token is unregistered or invalid.'], 404);
                }
                return response()->json(['error' => 'Failed to send notification: ' . $response['error']['message']], 400);
            }
    
            // If successful, return the response
            return response()->json(['success' => 'Notification sent successfully', 'response' => $response]);

            dd($response); // Output the response for debugging purposes
        }catch (Throwable $error) {
            report($error);
            return response()->json(['error' => 'Failed to send the notification due to an internal error.'], 500);

            // return back()->with('error', 'Failed to send the notification due to some internal error.');
        }
}

    /**
     * It will send notifications
     * @author Muhammad Abdullah Mirza
     * @version 1.1.0
     */
    // public function notificationSend(Request $request)
    // {
    //     try {
    //         $validatedData = notifications::validator($request);
    //         if ($validatedData->fails()) {
    //             flash('Error in sending notification because a required field is missing or invalid data.')->error();
    //             return Redirect::back()->withInput($request->input());
    //         }
    //         $firebaseToken = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
    //         $data = [
    //             "registration_ids" => $firebaseToken,
    //             "notification" => [
    //                 "title" => $request->title,
    //                 "body" => $request->body,
    //             ],
    //             "priority" => "high"
    //         ];
    //         $dataString = json_encode($data);
    //         $headers = [
    //             'Authorization: key=AAAAQ4iVuPM:APA91bGUp791v4RmZlEm3Dge71Yoj_dKq-XIytfnHtvCnHdmiH-BTZGlaCHGydnWvd976Mm5bSU6OFUNZqSf9YdamZifR3HMUl4m57RE21vSzrgGpfHmvYS47RQxDHV4WIN4zPFfNO-A',
    //             'Content-Type: application/json',
    //         ];
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    //         curl_exec($ch);
    //         curl_close($ch);
    //         return back()->with('success', 'Notification send successfully.');
    //     } catch (Throwable $error) {
    //         report($error);
    //         return back()->with('error', 'Failed to send the notification due to some internal error.');
    //     }
    // }
    /**
     * Test send notifications firebase API
     * @author Muhammad Abdullah Mirza
     * @version 1.0.0
     */
    public function notificationSendTest(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'title' => 'required|string',
                'body' => 'required|string'
            ]);
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
     * @author Muhammad Abdullah Mirza
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
