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
        Schema::create('promo_codes_usage_limit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('promo_code_id')->constrained(table: 'promo_codes')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->integer('total_used')->nullable();
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes_usage_limit');
    }
};
