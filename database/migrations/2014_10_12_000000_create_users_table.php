<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('l_name', 30)->nullable();
            $table->string('email')->unique();
            $table->longText('password')->nullable();
            $table->string('phone', 13)->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_phone', 13)->nullable();
            $table->json('business_hours')->nullable();
            $table->text('full_address')->nullable();
            $table->text('unit_address')->nullable();
            $table->string('country', 70)->nullable();
            $table->string('state', 70)->nullable();
            $table->string('city', 70)->nullable();
            $table->string('postcode', 11)->nullable();
            $table->decimal('lat', 11, 8)->nullable();
            $table->decimal('lon', 11, 8)->nullable();
            $table->json('bank_details')->nullable();
            $table->json('settings')->nullable();
            $table->text('user_img')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_online')->default(0);
            $table->string('remember_token', 100)->nullable();
            $table->tinyInteger('role_id')->comment('1: superadmin, 2: seller, 3: buyer, 5: child_seller');
            $table->double('pending_withdraw', 8, 2)->default(0.00);
            $table->double('total_withdraw', 8, 2)->default(0.00);
            $table->integer('parent_store_id')->nullable();
            $table->enum('vehicle_type', ['bike', 'car', 'van'])->nullable();
            $table->double('application_fee', 8, 2)->default(0.00);
            $table->string('temp_code', 6)->nullable();
            $table->string('referral_code')->nullable();
            $table->string('stripe_account_id')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->fullText('business_name');
            // $table->index('country');
            // $table->index('state');
            // $table->index('city');
            // $table->index('postcode');
            // $table->index('lat');
            // $table->index('lon');
            // $table->index('parent_store_id');
            // $table->index('referral_code');
            // $table->index('stripe_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
