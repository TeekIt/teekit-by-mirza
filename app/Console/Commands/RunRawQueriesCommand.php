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
                    // DB::statement('ALTER 
                    // TABLE orders_from_other_sellers 
                    // DROP FOREIGN KEY orders_from_other_sellers_parent_order_id_foreign');
                    // DB::statement('ALTER TABLE `order_items` CHANGE `product_belongs_to_id` `product_belongs_to_id` BIGINT NOT NULL');
                    // DB::statement('ALTER TABLE `order_items` CHANGE `product_price` `product_price` FLOAT NOT NULL');
                    
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
