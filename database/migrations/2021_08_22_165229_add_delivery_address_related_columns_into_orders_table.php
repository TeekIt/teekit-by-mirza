<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryAddressRelatedColumnsIntoOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('type', array('delivery', 'self-pickup'))->default('delivery')->after('lon');
            $table->string('receiver_name')->nullable()->after('type');
            $table->string('house_no')->nullable()->after('address');
            $table->string('flat')->nullable()->after('house_no');
            $table->text('description')->nullable()->after('flat');
            $table->float('delivery_charges')->nullable()->after('driver_charges');
            $table->float('service_charges')->nullable()->after('delivery_charges');
            DB::statement('ALTER TABLE `orders` MODIFY `address` VARCHAR(191) NULL;');
            DB::statement('ALTER TABLE `orders` MODIFY `phone_number` VARCHAR(191) NULL;');
            DB::statement('ALTER TABLE `orders` MODIFY `lat` VARCHAR(191) NULL;');
            DB::statement('ALTER TABLE `orders` MODIFY `lon` VARCHAR(191) NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('receiver_name');
            $table->dropColumn('house_no');
            $table->dropColumn('flat');
            $table->dropColumn('description');
            $table->dropColumn('delivery_charges');
            $table->dropColumn('service_charges');
            DB::statement('ALTER TABLE `orders` MODIFY `address` VARCHAR(191) NOT NULL;');
            DB::statement('ALTER TABLE `orders` MODIFY `phone_number` VARCHAR(191) NOT NULL;');
            DB::statement('ALTER TABLE `orders` MODIFY `lat` VARCHAR(191) NOT NULL;');
            DB::statement('ALTER TABLE `orders` MODIFY `lon` VARCHAR(191) NOT NULL;');
        });
    }
}
