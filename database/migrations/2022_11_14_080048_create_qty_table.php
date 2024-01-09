<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQtyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qty', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id');            
            // $table->foreign('users_id')->references('id')->on('users');
            $table->unsignedBigInteger('products_id');
            // $table->foreign('products_id')->references('id')->on('products');
            $table->unsignedBigInteger('category_id');
            // $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('qty');
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
        Schema::dropIfExists('qty');
    }
}
