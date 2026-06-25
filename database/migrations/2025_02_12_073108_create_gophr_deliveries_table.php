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
        Schema::create('gophr_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('order_belongs_to');  /* This column can either belong to "orders", "orders_from_other_sellers", or "van_inventory_orders" */
            $table->uuid('job_id');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gophr_deliveries');
    }
};
