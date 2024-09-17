<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateJwtTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jwt_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->constrained(table:'users')->cascadeOnDelete();
            $table->text('token');
            $table->string('browser');
            $table->string('platform');
            $table->string('device');
            $table->boolean('desktop')->default('0');
            $table->boolean('phone')->default('0');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::drop('jwt_tokens');
    }
}
