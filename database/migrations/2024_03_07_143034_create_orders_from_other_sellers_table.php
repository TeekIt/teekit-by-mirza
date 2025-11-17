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
        Schema::create('orders_from_other_sellers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('created_by', 'orders_from_other_sellers_created_by'); /* This column can either belong to "users" or "guest_buyers" */
            $table->foreignId('seller_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->foreignId('parent_order_id')->constrained(table: 'orders');
            $table->morphs('product_belongs_to', 'order_from_other_sellers_product_belongs_to'); /* This column can either belong to "products" or "products_by_buyers" */
            $table->float('product_price');
            $table->integer('product_qty');
            $table->float('initial_total');
            $table->float('current_total');
            $table->decimal('customer_lat', 11, 8)->nullable();
            $table->decimal('customer_lon', 11, 8)->nullable();
            $table->string('device', 7)->nullable()->comment('iPhone, Android');
            $table->string('type')->comment('Only OrderTypeEnum values are allowed');
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
            $table->string('payment_status')->comment('paid, hidden');
            $table->enum('order_status', ['pending', 'accepted', 'ready', 'stuartDelivery', 'onTheWay', 'delivered', 'complete', 'cancelled'])->default('pending');
            $table->enum('delivery_status', ['assigned', 'complete', 'pending_approval', 'cancelled'])->nullable();
            $table->string('payment_intent_id');
            $table->foreignId('driver_id')->nullable()->constrained(table: 'drivers')->cascadeOnDelete();
            $table->double('driver_traveled_km', 8, 2)->default(0.00);
            $table->double('driver_charges', 8, 2)->default(0.00);
            $table->tinyInteger('driver_charges_cleared')->default(0);
            $table->double('delivery_charges')->nullable();
            $table->double('service_charges')->nullable();
            $table->tinyInteger('offloading')->nullable()->comment('0: No, 1: Yes');
            $table->double('offloading_charges', 10, 2)->nullable();
            $table->time('estimated_time')->nullable();
            $table->tinyInteger('is_viewed')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('disabled')->default(0)->comment('Only ModelDisabledStatusEnum values are allowed');
            $table->tinyInteger('times_rejected')->default(0);
            $table->timestamp('moved_at');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('seller_id');
            $table->index('payment_intent_id');
            $table->index('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_from_other_sellers');
    }
};
