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
            $table->integer('user_id');
            $table->integer('category_id');
            $table->string('product_name');
            $table->string('sku');
            $table->string('price');
            $table->string('discount_percentage');
            $table->string('dimension')->nullable();
            $table->string('weight')->nullable();
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('status')->nullable();
            $table->string('contact');
            $table->json('colors')->nullable();
            $table->string('bike')->nullable();
            $table->string('van')->nullable();
            $table->string('feature_img')->nullable();
            $table->timestamps();
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
