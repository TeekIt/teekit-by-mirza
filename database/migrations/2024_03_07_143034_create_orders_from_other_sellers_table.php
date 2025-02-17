<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_from_other_sellers', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->foreignId('customer_id')->constrained(table:'users')->cascadeOnDelete();
            $table->morphs('created_by'); /* This column can either belong to "users" or "guest_buyers" */
            $table->foreignId('seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('parent_order_id')->constrained(table:'orders')->cascadeOnDelete();
            $table->morphs('product_belongs_to'); /* This column can either belong to "products" or "products_by_buyers" */
            $table->float('product_price');
            $table->integer('product_qty');
            $table->float('initial_total');
            $table->decimal('customer_lat', 11, 8)->nullable();
            $table->decimal('customer_lon', 11, 8)->nullable();
            $table->string('device', 7)->nullable()->comment('iPhone, Android');
            $table->enum('type', ['delivery', 'self-pickup'])->default('delivery');
            $table->string('customer_name', 191)->nullable();
            $table->string('phone_number', 191)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('house_no', 191)->nullable();
            $table->string('flat', 191)->nullable();
            $table->string('country', 70)->nullable();
            $table->string('state', 70)->nullable();
            $table->string('city', 70)->nullable();
            $table->string('postcode', 11)->nullable();
            $table->text('description')->nullable();
            $table->string('payment_status', 191)->comment('paid, hidden');
            $table->enum('order_status', ['pending', 'accepted', 'ready', 'stuartDelivery', 'onTheWay', 'delivered', 'complete', 'cancelled'])->default('pending');
            $table->enum('delivery_status', ['assigned', 'complete', 'pending_approval', 'cancelled'])->nullable();
            $table->string('payment_intent_id');
            $table->foreignId('driver_id')->nullable()->constrained(table:'drivers')->cascadeOnDelete();
            $table->double('driver_traveled_km', 8, 2)->default(0.00);
            $table->double('driver_charges', 8, 2)->default(0.00);
            $table->tinyInteger('driver_charges_cleared')->default(0);
            $table->double('delivery_charges')->nullable();
            $table->double('service_charges')->nullable();
            $table->tinyInteger('offloading')->nullable()->comment('0: No, 1: Yes');
            $table->double('offloading_charges', 10, 2)->nullable();
            $table->time('estimated_time')->nullable();
            $table->tinyInteger('is_viewed')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('accepted')->default(0)->comment('0: No, 1: Yes');
            $table->tinyInteger('times_rejected')->default(0);
            $table->timestamp('moved_at');
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('created_by_id');
            $table->index('seller_id');
            $table->index('product_belongs_to_id');
            $table->index('payment_intent_id');
            $table->index('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_from_other_sellers');
    }
};
