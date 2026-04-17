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
        Schema::create('inventory_order_items', function (Blueprint $table) {
           $table->bigIncrements('id'); 
           $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')
              ->cascadeOnDelete();
           $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
           $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
           $table->integer('quantity')->default(1);
           $table->decimal('price', 10, 2)->nullable();
           $table->foreignId('van_id')->nullable()->constrained('vans')
          ->nullOnDelete();
           $table->enum('status', ['pending', 'completed', 'cancelled'])
          ->default('pending');
           $table->timestamps();
           $table->softDeletes();

    // Indexes
           $table->index(['order_id', 'product_id']);
           $table->index(['seller_id', 'status']);
           $table->index(['van_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_order_items');
    }
};
