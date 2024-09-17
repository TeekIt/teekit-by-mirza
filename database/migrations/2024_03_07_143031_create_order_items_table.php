<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained(table:'orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained(table:'products')->cascadeOnDelete();
            $table->integer('product_price');
            $table->integer('product_qty');
            $table->tinyInteger('user_choice')->nullable()->comment('1-Alternative product that does the job, 2-Remove only this product from order, 3-Search for product in other stores, 4-Request a call from the store, 5-Cancel the order');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
