<?php

use App\Enums\PackagePickUpVehicleEnum;
use App\Enums\PackageTransportTypeEnum;
use App\Enums\PackageWeightEnum;
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
            $table->text('unit_address')->nullable();
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_email');
            $table->enum('package_transport_type', array_column(PackageTransportTypeEnum::cases(), 'value'));
            $table->enum('package_weight', array_column(PackageWeightEnum::cases(), 'value'));
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
