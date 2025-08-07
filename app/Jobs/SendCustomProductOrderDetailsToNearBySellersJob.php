<?php

namespace App\Jobs;

use App\Orders;
use App\Services\EmailServices;
use App\Services\GoogleMapServices;
use App\User;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendCustomProductOrderDetailsToNearBySellersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected float $buyerLat,
        protected float $buyerLon,
        protected User $seller,
        protected Orders $order
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $nearbySellers = GoogleMapServices::getNearBySellers(
            $this->buyerLat,
            $this->buyerLon,
            User::getParentAndChildSellersByCity($this->seller->city),
            $this->seller->id
        );

        if ($nearbySellers) {
            // EmailServices::sendCustomProductOrderDetailsToNearBySellersMail(
            //     array_merge(array_column($nearbySellers, 'email'), [
            //         'mirzaabdullahizhar@gmail.com',
            //         'azim.the.g8@googlemail.com',
            //         'zim_raja@hotmail.com',
            //         'info@msauctions.co.uk',
            //         'meeshatariq@gmail.com',
            //         'yasirtariqg@gmail.com',
            //     ]),
            //     $this->order
            // );

            EmailServices::sendCustomProductOrderDetailsToNearBySellersMail(
                [
                    'mirzaabdullahizhar@gmail.com',
                    'azim.the.g8@googlemail.com',
                    'zim_raja@hotmail.com',
                    'info@msauctions.co.uk',
                    'meeshatariq@gmail.com',
                    'yasirtariqg@gmail.com',
                ],
                $this->order
            );
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        new Exception($exception);
    }
}
