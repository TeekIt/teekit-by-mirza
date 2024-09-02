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
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained(table:'users')->cascadeOnDelete()->nullable();
            $table->text('device_id');
            $table->text('device_token');
            $table->timestamps();
            /**
             * Indexes
             */
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_tokens');
    }
};
