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
        Schema::create('requested_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('creator_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->string('delivery_provider')->comment('Only DeliveryProviderEnum values are allowed');
            $table->string('delivery_id');
            $table->text('pickup_address');
            $table->text('dropoff_address');
            $table->text('unit_address')->nullable();
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_email');
            $table->string('package_transport_type')->comment('Only PackageTransportTypeEnum values are allowed');
            $table->string('package_weight')->comment('Only PackageWeightEnum values are allowed');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requested_deliveries');
    }
};
