<?php

namespace App\Services;

use Twilio\Rest\Client;

final class TwilioSmsServices
{
    /**
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public static function sendSms(string $receiverNumber, string $message)
    {
        $sid = config('twilio.TWILIO_SID');
        $token = config('twilio.TWILIO_TOKEN');
        $fromNumber = config('twilio.TWILIO_FROM');

        $client = new Client($sid, $token);
        $client->messages->create($receiverNumber, [
            'from' => $fromNumber,
            'body' => $message,
        ]);
    }
}
