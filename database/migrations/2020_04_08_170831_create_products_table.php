<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained(table:'categories')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('sku');
            $table->float('price');
            $table->tinyInteger('featured')->default(0)->comment('0:not_featured, 1:featured');
            $table->string('discount_percentage');
            $table->string('weight')->nullable();
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('status')->nullable();
            $table->string('contact');
            $table->json('colors')->nullable();
            $table->tinyInteger('bike')->nullable();
            $table->tinyInteger('car')->nullable();
            $table->tinyInteger('van')->nullable();
            $table->text('feature_img')->nullable();
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->float('length')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
        
    }
}
