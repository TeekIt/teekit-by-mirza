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
        Schema::create('commission_and_service_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->json('commission')->nullable();
            $table->json('service_fee')->nullable();
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
        Schema::dropIfExists('commission_and_service_fees');
    }
};
