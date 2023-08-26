<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOffloadingColumnsIntoOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('offloading')->nullable()->comment('0: No, 1: Yes')->after('service_charges');
            $table->float('offloading_charges', 10, 2)->nullable()->after('offloading');
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
            $table->dropColumn('offloading');
            $table->dropColumn('offloading_charges');
        });
    }
}
