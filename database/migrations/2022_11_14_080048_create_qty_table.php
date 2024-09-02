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
            $table->foreignId('product_id')->constrained(table:'products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained(table:'categories')->cascadeOnDelete();
            $table->integer('qty');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('seller_id');
            $table->index('product_id');
            $table->index('category_id');
            $table->index('qty');
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
