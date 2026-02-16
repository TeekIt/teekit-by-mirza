<?php

namespace Tests\Feature;

use App\Services\TwilioSmsServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TwilioSmsServicesTest extends TestCase
{
    // use RefreshDatabase;
    use WithFaker;

    public function testSendSmsIsWorking(): void
    {
        TwilioSmsServices::sendSms(
            receiverNumber: '+923170155625',
            message: 'Your order is ready for pickup!'
        );

        $this->assertTrue(true);
    }

    public function testSendPlainWhatsAppMessageIsWorking(): void
    {
        TwilioSmsServices::sendPlainWhatsAppMessage(
            receiverNumber: '+923170155625',
            message: 'Your order is ready for pickup!'
        );

        $this->assertTrue(true);
    }

    public function testSendWhatsAppMessageWithMediaIsWorking(): void
    {
        TwilioSmsServices::sendWhatsAppMessageWithMedia(
            receiverNumber: '+923170155625',
            message: 'Your order is ready for pickup!',
            mediaUrl: ['https://images.unsplash.com/photo-1545093149-618ce3bcf49d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=668&q=80']
        );

        $this->assertTrue(true);
    }
}
