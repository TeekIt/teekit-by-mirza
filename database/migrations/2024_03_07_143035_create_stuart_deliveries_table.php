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
        Schema::create('stuart_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained(table: 'orders')->cascadeOnDelete();
            $table->bigInteger('job_id')->comment('Stuart job id');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('order_id');
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stuart_deliveries');
    }
};
