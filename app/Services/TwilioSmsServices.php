<?php

namespace App\Services;

use Twilio\Rest\Client;

final class TwilioSmsServices
{
    public static function getTwilioClient(): Client
    {
        return new Client(
            config('twilio.TWILIO_SID'),
            config('twilio.TWILIO_TOKEN')
        );
    }

    public static function getFromNumber(): string
    {
        return config('twilio.TWILIO_FROM_NUMBER');
    }

    /**
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public static function sendSms(string $receiverNumber, string $message): void
    {
        self::getTwilioClient()->messages->create(
            to: $receiverNumber,
            options: [
                'from' => config('twilio.TWILIO_FROM'),
                'body' => $message,
            ]
        );
    }

    public static function sendPlainWhatsAppMessage(string $receiverNumber, string $message): void
    {
        self::getTwilioClient()->messages->create(
            to: 'whatsapp:' . $receiverNumber,
            options: [
                'from' => 'whatsapp:' . self::getFromNumber(),
                'body' => $message,
            ]
        );
    }

    public static function sendWhatsAppMessageWithMedia(string $receiverNumber, string $message, string $mediaUrl): void
    {
        self::getTwilioClient()->messages->create(
            to: 'whatsapp:' . $receiverNumber,
            options: [
                'from' => 'whatsapp:' . self::getFromNumber(),
                "mediaUrl" => [$mediaUrl],
                'body' => $message,
            ]
        );
    }
}
