<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained(table: 'users')->cascadeOnDelete();
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
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
