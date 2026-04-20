<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_inventory_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->foreignId('van_id')->constrained(table: 'vans')->cascadeOnDelete();
            $table->float('order_total');
            $table->string('order_status')->comment('Only OrderStatusEnum values are allowed');
            $table->string('type')->comment('Only OrderTypeEnum values are allowed');
            $table->text('van_location');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_inventory_orders');
    }
};
