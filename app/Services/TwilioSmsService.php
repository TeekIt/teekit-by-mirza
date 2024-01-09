<?php

namespace App\Services;

use Twilio\Rest\Client;

final class TwilioSmsService
{
    /**
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public static function sendSms($receiverNumber, $message)
    {
        $sid = config("app.TWILIO_SID");
        $token = config("app.TWILIO_TOKEN");
        $fromNumber = config("app.TWILIO_FROM");
        
        $client = new Client($sid, $token);
        $client->messages->create($receiverNumber, [
            'from' => $fromNumber,
            'body' => $message
        ]);
    }
}
