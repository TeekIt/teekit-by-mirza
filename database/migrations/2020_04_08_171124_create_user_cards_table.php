<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained(table:'users')->cascadeOnDelete();
            $table->string('card_placeholder_name');
            $table->string('card_number');
            $table->string('cvv');
            $table->string('exp_date');
            $table->timestamp('last_time_charge_date')->nullable();
            $table->string('last_time_charge_amount')->nullable();
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
        Schema::dropIfExists('user_cards');
    }
}
