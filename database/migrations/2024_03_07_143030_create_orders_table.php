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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('created_by'); /* This column can either belong to "users" or "guest_buyers" */
            $table->foreignId('seller_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->float('initial_total');
            $table->float('current_total');
            $table->tinyInteger('total_items');
            $table->decimal('customer_lat', 11, 8)->nullable();
            $table->decimal('customer_lon', 11, 8)->nullable();
            $table->string('device', 7)->nullable()->comment('iPhone, Android');
            $table->string('type')->comment('Only OrderTypeEnum values are allowed');
            // $table->dateTime('scheduled_at')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('country_code', 4)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('address')->nullable();
            $table->string('house_no')->nullable();
            $table->string('flat')->nullable();
            $table->string('country', 70)->nullable();
            $table->string('state', 70)->nullable();
            $table->string('city', 70)->nullable();
            $table->string('postcode', 11)->nullable();
            $table->text('description')->nullable();
            $table->enum('payment_status', ['paid, hidden']);
            $table->enum('order_status', ['pending', 'accepted', 'ready', 'stuartDelivery', 'onTheWay', 'delivered', 'complete', 'cancelled'])->default('pending');
            $table->enum('delivery_status', ['assigned', 'pending_approval', 'complete', 'cancelled'])->nullable();
            $table->string('payment_intent_id');
            $table->foreignId('driver_id')->nullable()->constrained(table: 'drivers')->cascadeOnDelete();
            $table->double('driver_traveled_km', 8, 2)->default(0.00);
            $table->double('driver_charges', 8, 2)->default(0.00);
            $table->tinyInteger('driver_charges_cleared')->default(0);
            $table->double('delivery_charges')->nullable();
            $table->double('service_charges')->nullable();
            $table->tinyInteger('offloading')->nullable()->comment('0: No, 1: Yes');
            $table->double('offloading_charges', 10, 2)->nullable();
            $table->tinyInteger('is_viewed')->default(0)->comment('0: No, 1: Yes');
            $table->timestamp('moved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('seller_id');
            $table->index('order_status');
            $table->index('delivery_status');
            $table->index('payment_intent_id');
            $table->index('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
