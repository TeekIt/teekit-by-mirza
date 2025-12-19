<?php

namespace App\Jobs;

use App\Actions\MoveOrderToOtherNearBySellersAction;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use DateTime;
use Exception;
use Throwable;

class MoveOrderToOtherNearBySellersJob implements ShouldQueue
{
    use Queueable;

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
    public function __construct(private Orders $order, private User $seller) {}

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
    public function handle(MoveOrderToOtherNearBySellersAction $moveOrderToOtherNearBySellersAction): void
    {
        $moveOrderToOtherNearBySellersAction->execute($this->order, $this->seller);
    }
}
