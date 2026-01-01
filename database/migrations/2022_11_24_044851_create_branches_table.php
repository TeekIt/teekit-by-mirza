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
        Schema::create('branches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('parent_seller_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->foreignId('child_seller_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('parent_seller_id');
            $table->index('child_seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
