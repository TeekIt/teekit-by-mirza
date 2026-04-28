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

    protected bool $executeQueries = true;

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

                    /* Below queries are already executed on staging ENV */

                    /* Below queries are already executed on local ENV */

                    // DB::statement("ALTER TABLE qty ADD INDEX qty_seller_id_product_id_index (seller_id, product_id)");

                    // DB::statement("ALTER TABLE `van_inventory_orders` RENAME COLUMN `van_location` TO `address`;");

                    // DB::statement("
                    //     ALTER TABLE `van_inventory_orders`
                    //     ADD COLUMN `customer_name` VARCHAR(255) NULL AFTER `type`,
                    //     ADD COLUMN `customer_lat` DECIMAL(11,8) NOT NULL AFTER `customer_name`,
                    //     ADD COLUMN `customer_lon` DECIMAL(11,8) NOT NULL AFTER `customer_lat`,
                    //     ADD COLUMN `country_code` VARCHAR(4) NOT NULL AFTER `customer_lon`,
                    //     ADD COLUMN `phone_number` VARCHAR(255) NOT NULL AFTER `country_code`
                    // ");

                    // DB::statement("
                    //     ALTER TABLE `van_inventory_orders`
                    //     ADD COLUMN `country` VARCHAR(70) NOT NULL AFTER `address`,
                    //     ADD COLUMN `state` VARCHAR(70) NOT NULL AFTER `country`,
                    //     ADD COLUMN `city` VARCHAR(70) NOT NULL AFTER `state`,
                    //     ADD COLUMN `postcode` VARCHAR(11) NOT NULL AFTER `city`
                    // ");
                });
            }
        } catch (Exception $error) {
            report($error);
            $this->error($error->getMessage());
        }
    }
}
