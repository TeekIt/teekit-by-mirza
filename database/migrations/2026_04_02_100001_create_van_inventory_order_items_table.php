<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_inventory_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('van_inventory_order_id')->constrained(table: 'van_inventory_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained(table: 'products')->cascadeOnDelete();
            $table->float('product_price');
            $table->integer('product_qty');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_inventory_order_items');
    }
};
