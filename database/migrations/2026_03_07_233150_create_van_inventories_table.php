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
        Schema::create('van_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('seller_name');
            $table->foreignId('category_id')->constrained(table: 'categories')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->float('price');
            $table->tinyInteger('featured')->default(0)->comment('0:not_featured, 1:featured');
            $table->string('discount_percentage');
            $table->float('weight')->nullable();
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->enum('status', [0, 1])->comment('0: disable, 1: enable')->nullable();
            $table->string('contact');
            $table->json('colors')->nullable();
            $table->tinyInteger('bike')->nullable();
            $table->tinyInteger('car')->nullable();
            $table->tinyInteger('van')->nullable();
            $table->text('feature_img');
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->float('length')->nullable();
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('category_id');
            $table->fullText('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('van_inventories');
    }
};
