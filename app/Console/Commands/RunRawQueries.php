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

    protected bool $executeQueries = true;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            if ($this->executeQueries) {
                $this->warn('You are executing raw queries directly into the database');

                DB::transaction(function () {
                    /* Below queries are already executed on production & all other ENV */
                    // DB::statement("ALTER TABLE `promo_codes` CHANGE `store_id` `store_id` BIGINT UNSIGNED NULL DEFAULT NULL");
                    // DB::statement("ALTER TABLE `promo_codes` ADD `free_delivery` BOOLEAN NOT NULL DEFAULT FALSE AFTER `store_id`");

                    DB::statement("ALTER TABLE `products_by_buyers`
                                   ADD COLUMN `category_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `seller_id`,
                                   ADD CONSTRAINT `products_by_buyers_category_id_foreign`
                                   FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE");

                    DB::statement("ALTER TABLE `orders_from_other_sellers` CHANGE `accepted` `disabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only ModelStatusEnum values are allowed'");
                    
                    DB::statement("ALTER TABLE `orders_from_other_sellers` ADD COLUMN `current_total` FLOAT NOT NULL DEFAULT 0.00 AFTER `initial_total`");

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
