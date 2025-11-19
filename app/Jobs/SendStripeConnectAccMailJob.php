<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EmailServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use DateTime;
use Exception;

class SendStripeConnectAccMailJob implements ShouldQueue
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
    public function __construct(public User $user) {}

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
        EmailServices::sendStripeConnectAccMail($this->user);
    }
}
