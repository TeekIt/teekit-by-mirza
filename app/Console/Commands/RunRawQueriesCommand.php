<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunRawQueriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:raw-queries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes all raw queries provided in the handle method';

    protected bool $executeQueries = false;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            if ($this->executeQueries) {
                $this->warn('You are executing raw queries directly into the database');

                DB::transaction(function () {
                    /* Below queries are already executed on production & all other ENV */
                    DB::statement('ALTER TABLE requested_deliveries ADD COLUMN total_cost FLOAT NOT NULL DEFAULT 0.0 AFTER package_weight');

                    /* Below queries are already executed on staging ENV */

                    /* Below queries are already executed on local ENV */
                });
            }
        } catch (Exception $error) {
            report($error);
            $this->error($error->getMessage());
        }
    }
}
