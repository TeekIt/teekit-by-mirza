<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_by_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('created_by')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->string('product_name');
            $table->float('max_price');
            $table->float('weight')->nullable();
            $table->string('brand')->nullable();
            $table->string('part_number')->nullable();
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
        Schema::dropIfExists('products_by_customers');
    }
};
