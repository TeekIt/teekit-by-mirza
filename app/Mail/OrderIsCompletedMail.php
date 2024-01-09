<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderIsCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userType;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userType)
    {
        $this->userType = $userType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $userType = $this->userType;
        return $this->view('emails.order_is_completed', compact('userType'))
            ->subject("Your Order Has Been Completed Successfully");
    }
}
