<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StoreRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $html;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @param $html
     * @param $subject
     */
    public function __construct($html, $subject)
    {
        $this->html = $html;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $html = $this->html;
        return $this->view('emails.general', compact('html'))
            ->subject($this->subject);
    }
}
