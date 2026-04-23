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
        Schema::create('van_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('seller_id')->constrained(table: 'users');
            $table->foreignId('category_id')->constrained(table: 'categories');
            $table->foreignId('van_id')->constrained(table: 'vans');
            $table->string('product_name');
            $table->string('sku');
            $table->float('price');
            $table->tinyInteger('featured')->default(0)->comment('0:not_featured, 1:featured');
            $table->string('discount_percentage');
            $table->float('weight')->nullable();
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('status')->default('1')->comment('Only VanProductStatusEnum values are allowed');
            $table->string('contact');
            $table->json('colors')->nullable();
            $table->tinyInteger('bike')->nullable();
            $table->tinyInteger('car')->nullable();
            $table->tinyInteger('van')->nullable();
            $table->text('feature_img');
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->float('length')->nullable();
            $table->string('job_reference')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('min_threshold')->default(5)->comment('Minimum stock threshold for inventory alerts');
            $table->timestamps();
            $table->softDeletes();

            /**
             * Indexes
             */
            $table->index('seller_id');
            $table->index('category_id');
            $table->fullText('product_name');
            $table->index('job_reference');
            $table->index('van_id');
            $table->index('quantity');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('van_products');
    }
};
