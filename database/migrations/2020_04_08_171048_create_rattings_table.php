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
        Schema::create('rattings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('product_id')->constrained(table: 'products')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->float('ratting');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('product_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rattings');
    }
};
