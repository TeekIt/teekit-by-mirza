<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('customer_id')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained(table:'users')->cascadeOnDelete();
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
            $table->enum('payment_status', ['paid, hidden']);
            $table->enum('order_status', ['pending', 'accepted', 'ready', 'stuartDelivery', 'onTheWay', 'delivered', 'complete', 'cancelled'])->default('pending');
            $table->enum('delivery_status', ['assigned', 'pending_approval', 'complete', 'cancelled'])->nullable();
            $table->text('payment_intent_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();
            $table->double('driver_traveled_km', 8, 2)->default(0.00);
            $table->double('driver_charges', 8, 2)->default(0.00);
            $table->tinyInteger('driver_charges_cleared')->default(0);
            $table->double('delivery_charges')->nullable();
            $table->double('service_charges')->nullable();
            $table->tinyInteger('offloading')->nullable()->comment('0: No, 1: Yes');
            $table->double('offloading_charges', 10, 2)->nullable();
            $table->tinyInteger('is_viewed')->default(0)->comment('0: No, 1: Yes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}