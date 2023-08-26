<?php

namespace App\Services;

use Twilio\Rest\Client;

final class TwilioSmsService
{

    private $sid;
    private $token;
    private $fromNumber;

    public function __construct()
    {
        $this->sid = config("app.TWILIO_SID");
        $this->token = config("app.TWILIO_TOKEN");
        $this->fromNumber = config("app.TWILIO_FROM");
    }

    /**
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function sendSms($receiverNumber, $message)
    {
        $client = new Client($this->sid, $this->token);
        $client->messages->create($receiverNumber, [
            'from' => $this->fromNumber,
            'body' => $message
        ]);
    }
}
