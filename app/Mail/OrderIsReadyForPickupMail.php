<?php

namespace App\Mail;

use App\Models\Orders;
use App\Models\OrdersFromOtherSeller;
use App\Models\User;
use App\Models\VanInventoryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderIsReadyForPickupMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(private Orders|OrdersFromOtherSeller|VanInventoryOrder $order, private User $user) {}

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Your Order #'.$this->order->id.' Is Ready To Be Picked Up - '.config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.order_is_ready_for_pickup',
            with: [
                'order' => $this->order,
                'seller' => $this->user,
                'pinLocation' => 'https://www.google.com/maps?q='.$this->user->lat.','.$this->user->lon,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
