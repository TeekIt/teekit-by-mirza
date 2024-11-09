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
        Schema::create('guest_buyers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('f_name', 100);
            $table->string('l_name', 100);
            $table->string('email')->unique();
            $table->string('country_code', 4);
            $table->string('phone', 13);
            $table->text('full_address');
            $table->text('unit_address')->nullable();
            $table->string('country', 70);
            $table->string('state', 70);
            $table->string('city', 70);
            $table->string('postcode', 11)->nullable();
            $table->decimal('lat', 11, 8);
            $table->decimal('lon', 11, 8);
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
        Schema::dropIfExists('guest_buyers');
    }
};
