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
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('product_id');
            $table->float('product_price');
            $table->integer('product_qty');
            $table->float('order_total');
            $table->tinyInteger('total_items');
            $table->decimal('customer_lat', 11, 8)->nullable();
            $table->decimal('customer_lon', 11, 8)->nullable();
            $table->string('device', 7)->nullable()->comment('iPhone, Android');
            $table->enum('type', ['delivery', 'self-pickup'])->default('delivery');
            $table->string('customer_name', 191)->nullable();
            $table->string('phone_number', 191)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('house_no', 191)->nullable();
            $table->string('flat', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('payment_status', 191)->comment('paid, hidden');
            $table->enum('order_status', ['pending', 'accepted', 'ready', 'stuartDelivery', 'onTheWay', 'delivered', 'complete', 'cancelled'])->default('pending');
            $table->enum('delivery_status', ['assigned', 'complete', 'pending_approval', 'cancelled'])->nullable();
            $table->text('payment_intent')->nullable();
            $table->unsignedInteger('driver_id')->nullable();
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
            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraints
            // $table->foreign('customer_id')->references('id')->on('users');
            // $table->foreign('seller_id')->references('id')->on('users');
            // $table->foreign('product_id')->references('id')->on('products');
            // $table->foreign('driver_id')->references('id')->on('drivers');
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