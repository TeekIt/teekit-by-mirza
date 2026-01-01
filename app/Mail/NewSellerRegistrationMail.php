<?php

namespace App\Mail;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSellerRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected readonly string $sellerRoleName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public User $user,
        public UserRoleEnum $sellerType,
        public string $accountVerificationLink,
        public ?string $parentSeller = null,
    ) {
        $this->afterCommit();

        $this->sellerRoleName = $this->getSellerRoleName($this->sellerType);
    }

    public function getSellerRoleName(UserRoleEnum $sellerType): string
    {
        return ($sellerType === UserRoleEnum::SELLER) ? 'Parent' : 'Child';
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'A New ' . $this->sellerRoleName . ' Seller Has Been Registered 🥳 - Verification Required',
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
            markdown: 'emails.new_seller_registration',
            with: [
                'seller' => $this->user,
                'sellerRoleName' => $this->sellerRoleName,
                'accountVerificationLink' => $this->accountVerificationLink,
                'parentSeller' => $this->parentSeller,
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
