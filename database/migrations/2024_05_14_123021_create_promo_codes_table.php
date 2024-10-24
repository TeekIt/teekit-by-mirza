<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('promo_code', 20);
            $table->tinyInteger('discount_type')->comment('0:percentage, 1:fixed amount.');
            $table->float('discount', 10, 2);
            $table->bigInteger('order_number')->nullable()->comment('If the promo code is for a specific order number.');
            $table->integer('usage_limit')->nullable()->comment('How many times this promo code can be used.');
            $table->float('min_amnt_for_discount', 10, 2);
            $table->float('max_amnt_for_discount', 10, 2);
            $table->foreignId('store_id')->constrained(table:'users')->cascadeOnDelete()->nullable()->comment('If the promo code is for a specific store.');
            $table->date('expiry_dt');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('promo_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promo_codes');
    }
};
