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
            $table->foreignId('seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('products_id')->constrained(table:'products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained(table:'categories')->cascadeOnDelete();
            $table->integer('qty');
            $table->index(['users_id', 'products_id', 'category_id', 'qty']);
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
        Schema::dropIfExists('qty');
    }
}
