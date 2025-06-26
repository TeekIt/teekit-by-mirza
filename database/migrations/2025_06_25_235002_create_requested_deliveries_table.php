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
            $table->foreignId('creator_id')->constrained(table:'users')->cascadeOnDelete();
            $table->text('pickup_address');
            $table->text('dropoff_address');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->enum('package_size', ['Moped', 'Car Boot', 'Small Van', 'Big Van']);
            $table->enum('package_weight', ['< 5Kg', '< 10Kg', '< 15Kg', '> 15Kg']);
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
