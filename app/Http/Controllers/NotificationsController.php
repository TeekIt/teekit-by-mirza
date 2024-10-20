<?php

namespace App\Http\Controllers;

use App\DeviceToken;
use App\Services\JsonResponseServices;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Google\Client;
use Throwable;

class NotificationsController extends Controller
{
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
        $validatedData = Validator::make($request->all(), [
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        if ($validatedData->fails()) {
            return back()->with('error', $validatedData->errors()->first());
        }

        try {
            /* Path to your service account JSON key file */
            $serviceAccountPath = storage_path('app/googleFcmServices/teek-it-965a8-1ec96a1aa676.json');
            $accessToken = $this->getAccessToken($serviceAccountPath);

            /* Your Firebase project ID */
            $projectId = 'teek-it-965a8';

            $firebaseTokens = DeviceToken::whereNotNull('device_token')->pluck('device_token')->all();
            if (empty($firebaseTokens)) {
                return back()->with('error', 'No valid device tokens available');
                // return response()->json(['error' => 'No valid device tokens available'], 400);
            }

            $message = [
                // 'token' => $firebaseTokens[0],  
                'notification' => [
                    'title' => $request->title,
                    'body' => $request->body,
                ],
            ];

            foreach ($firebaseTokens as $singleFirebaseToken) {

                $message['token'] = $singleFirebaseToken;
                $response = $this->sendMessage($accessToken, $projectId, $message);
                if (isset($response['error'])) {

                    if (
                        $response['error']['status'] === 'NOT_FOUND' &&
                        $response['error']['details'][0]['errorCode'] === 'UNREGISTERED'
                    ) {
                        DeviceToken::deleteByDeviceToken($singleFirebaseToken);
                    }

                    return back()->with('error', 'Failed to send notification: ' . $response['error']['message']);
                    // return response()->json(['error' => 'Failed to send notification: ' . $response['error']['message']], 400);
                }
            }

            // If successful, return the response
            return back()->with('success', 'Notification sent successfully');
            // return response()->json(['success' => 'Notification sent successfully', 'response' => $response]);

        } catch (Throwable $error) {
            report($error);
            // return response()->json(['error' => 'Failed to send the notification due to an internal error.'], 500);
            return back()->with('error', 'Failed to send the notification due to some internal error');
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
     * It will save/update device token of every user
     * @author Muhammad Abdullah Mirza
     * @version 1.0.0
     */
    public function saveToken(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'user_id' => 'integer',
                'device_id' => 'required|string',
                'device_token' => 'required|string'
            ]);
            if ($validatedData->fails()) {
                JsonResponseServices::getApiValidationFailedResponse($validatedData->errors());
            }

            DeviceToken::addOrUpdate(
                $request->user_id,
                $request->device_id,
                $request->device_token,
            );

            return JsonResponseServices::getApiResponse(
                [],
                config('constants.TRUE_STATUS'),
                config('constants.DATA_UPDATED_SUCCESS'),
                config('constants.HTTP_OK'),
            );
        } catch (Throwable $error) {
            report($error);
            return JsonResponseServices::getApiResponse(
                [],
                config('constants.FALSE_STATUS'),
                $error,
                config('constants.HTTP_SERVER_ERROR'),
            );
        }
    }
}
