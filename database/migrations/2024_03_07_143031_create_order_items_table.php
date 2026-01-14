<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained(table: 'orders')->cascadeOnDelete();
            $table->morphs('product_belongs_to'); /* This column can either belong to "products" or "products_by_buyers" */
            $table->float('product_price');
            $table->integer('product_qty');
            $table->tinyInteger('user_choice')->nullable()->comment('1-Alternative product that does the job, 2-Remove only this product from order, 3-Search for product in other stores, 4-Request a call from the store, 5-Cancel the order');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
