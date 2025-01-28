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
        $executeQueries = true;

        try {
            if ($executeQueries) {
                $this->warn('You are executing raw queries directly into the database');

                DB::transaction(function () {
                    /* Modification in "orders" table: */
                    
                    /* * Drop customer_id relation from "orders" table: */
                    // DB::statement('ALTER TABLE orders DROP FOREIGN KEY orders_customer_id_foreign'); 
                    
                    /* * Change "customer_id" to "created_by_id" and modify its data type: */
                    // DB::statement('ALTER TABLE `orders` CHANGE `customer_id` `created_by_id` BIGINT UNSIGNED NOT NULL'); 
                    
                    // /* * Add "created_by_type" column: */
                    // DB::statement('ALTER TABLE `orders` ADD `created_by_type` VARCHAR(191) NOT NULL AFTER `id`'); 
                    
                    // /* * Update "created_by_type" column to "User": */
                    // DB::statement('UPDATE orders SET created_by_type = \'User\'');

                    // DB::statement('ALTER TABLE `orders` CHANGE `order_total` `initial_total` DOUBLE(8,2) NOT NULL');
                    // DB::statement('ALTER TABLE `orders` ADD `current_total` DOUBLE(8,2) NOT NULL AFTER `initial_total`');
                    // DB::statement('UPDATE `orders` SET `current_total` = `initial_total`');
                    // DB::statement('ALTER TABLE `orders` ADD `moved_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_viewed`');

                    // DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_product_id_foreign');
                    // DB::statement('ALTER TABLE `order_items` ADD `product_belongs_to_type` VARCHAR(191) NOT NULL AFTER `order_id`');
                    // DB::statement('ALTER TABLE `order_items` CHANGE `product_id` `product_belongs_to_id` DOUBLE(8,2) NOT NULL');
                    // DB::statement('UPDATE order_items SET product_belongs_to_type = \'Product\'');

                    // DB::statement('ALTER TABLE `orders_from_other_sellers` DROP `total_items`');
                    // DB::statement('ALTER TABLE `orders_from_other_sellers` CHANGE `driver_id` `driver_id` BIGINT UNSIGNED NULL DEFAULT NULL');
                    // DB::statement('ALTER TABLE `orders_from_other_sellers` ADD `moved_at` TIMESTAMP NOT NULL AFTER times_rejected');

                    /* Above queries are already executed on Staging ENV */
                    DB::statement('DELETE FROM `orders` WHERE payment_intent_id IS NULL');
                    DB::statement('ALTER TABLE `orders` CHANGE `payment_intent_id` `payment_intent_id` VARCHAR(191) NOT NULL');

                    DB::statement('ALTER TABLE `orders_from_other_sellers` ADD `product_belongs_to_type` VARCHAR(191) NOT NULL AFTER `parent_order_id`');
                    DB::statement('UPDATE `orders_from_other_sellers` SET `product_belongs_to_type` = \'Product\'');
                    DB::statement('ALTER TABLE `orders_from_other_sellers` DROP FOREIGN KEY orders_from_other_sellers_product_id_foreign');
                    DB::statement('ALTER TABLE `orders_from_other_sellers` CHANGE `product_id` `product_belongs_to_id` BIGINT UNSIGNED NOT NULL');

                    DB::statement('ALTER TABLE `orders_from_other_sellers` DROP FOREIGN KEY orders_from_other_sellers_customer_id_foreign'); 
                    DB::statement('ALTER TABLE `orders_from_other_sellers` CHANGE `customer_id` `created_by_id` BIGINT UNSIGNED NOT NULL'); 
                    DB::statement('ALTER TABLE `orders_from_other_sellers` ADD `created_by_type` VARCHAR(191) NOT NULL AFTER `id`'); 
                    DB::statement('UPDATE `orders_from_other_sellers` SET created_by_type = \'User\'');

                    DB::statement('ALTER TABLE `orders_from_other_sellers` CHANGE `order_total` `initial_total` DOUBLE(8,2) NOT NULL');

                    DB::statement('ALTER TABLE `users` CHANGE `temp_code` `temp_code` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL');
                });

                $this->info('All raw queries are executed successfully');
            }
        } catch (Exception $error) {
            report($error);
            $this->error($error->getMessage());
        }
    }
}
