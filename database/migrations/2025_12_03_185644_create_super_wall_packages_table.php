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
        Schema::create('super_wall_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('super_fast_deliveries');
            $table->integer('unlimited_super_fast_deliveries_on_orders_above')->nullable();
            $table->string('currency', 4)->nullable();
            $table->integer('min_delivery_time_in_minutes')->default(30);
            $table->string('guarantee_delivery_time_in_minutes')->nullable();
            $table->string('cashback_percentage')->nullable();
            $table->string('van_deliveries_discount_percentage')->nullable();
            $table->string('priority_support')->default('0');
            $table->string('users_included')->nullable();
            $table->string('free_same_day_delivery')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_wall_packages');
    }
};
