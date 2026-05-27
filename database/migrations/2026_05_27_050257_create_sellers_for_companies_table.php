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
        Schema::create('sellers_for_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained(table: 'products')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers_for_companies');
    }
};
