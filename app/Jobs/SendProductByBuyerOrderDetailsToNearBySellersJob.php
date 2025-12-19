<?php

namespace App\Jobs;

use App\Models\Orders;
use App\Services\EmailServices;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendProductByBuyerOrderDetailsToNearBySellersJob implements ShouldQueue
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
        private array $nearbySellers,
        private Orders $order
    ) {}

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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        EmailServices::sendProductByBuyerOrderDetailsToNearBySellersMail(
            array_column($this->nearbySellers, 'email'),
            $this->order
        );

        // EmailServices::sendProductByBuyerOrderDetailsToNearBySellersMail(
        //     [
        //         'zim_raja@hotmail.com',
        //         'mirzaabdullahizhar@gmail.com',
        //         'azim.the.g8@googlemail.com',
        //         'info@msauctions.co.uk',
        //         'meeshatariq@gmail.com',
        //         'yasirtariqg@gmail.com',
        //     ],
        //     $this->order
        // );
    }
}
