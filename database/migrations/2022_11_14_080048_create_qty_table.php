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
        Schema::create('qty', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('seller_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained(table: 'products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained(table: 'categories')->cascadeOnDelete();
            $table->integer('qty');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('seller_id');
            $table->index('product_id');
            $table->index('category_id');
            /* Composite index */
            $table->index(['seller_id', 'product_id'], 'qty_seller_id_product_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qty');
    }
};
