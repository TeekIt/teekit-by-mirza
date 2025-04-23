<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunRawQueries extends Command
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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $executeQueries = false;

        try {
            if ($executeQueries) {
                $this->warn('You are executing raw queries directly into the database');

                DB::transaction(function () {
                    
                    /* Above queries are already executed on all ENVs */
                    
                    /* Above queries are already executed on local ENV */

                });
            }
        } catch (Exception $error) {
            report($error);
            $this->error($error->getMessage());
        }
    }
}
