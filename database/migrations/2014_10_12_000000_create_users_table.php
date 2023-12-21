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
        // Schema::create('users', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->string('name');
        //     $table->string('l_name')->nullable()->default(null);
        //     $table->string('email')->unique();
        //     $table->string('password');
        //     $table->string('phone')->nullable()->default(null);
        //     $table->string('address_1')->nullable()->default(null);
        //     $table->string('address_2')->nullable()->default(null);
        //     $table->string('postal_code')->nullable()->default(null);
        //     $table->string('business_name')->nullable()->default(null);
        //     $table->string('business_phone')->nullable()->default(null);
        //     $table->json('business_location')->nullable()->default(null);
        //     $table->json('business_hours')->nullable()->default(null);
        //     $table->json('bank_details')->nullable()->default(null);
        //     $table->text('user_img')->nullable()->default(NULL);
        //     $table->datetime('last_login')->nullable();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->boolean('is_active')->default(0);
        //     $table->rememberToken();
        //     $table->timestamps();
        // });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); //modified
            $table->string('l_name', 30)->nullable(); //modified
            $table->string('email')->unique(); //modified
            $table->longText('password');
            $table->string('phone', 13)->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable(); //do we need it ??
            $table->string('country', 70)->index()->nullable(); //new
            $table->string('state', 70)->index()->nullable(); //new
            $table->string('city', 70)->index()->nullable(); //new
            $table->string('postcode', 10)->nullable(); //do we need it ??
            $table->string('business_name')->index()->nullable(); //modified
            $table->string('business_phone', 13)->nullable();
            // $table->json('business_location')->nullable(); //do we need it ??
            $table->decimal('lat', 11, 8)->index()->nullable(); //modified
            $table->decimal('lon', 11, 8)->index()->nullable(); //modified
            $table->json('business_hours')->nullable();
            $table->json('bank_details')->nullable();
            $table->json('settings')->nullable();
            $table->text('user_img')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->string('remember_token', 100)->nullable();
            $table->tinyInteger('role_id')->index()->comment('1: superadmin, 2: seller, 3: buyer, 5: child_seller'); //modified
            $table->double('pending_withdraw', 8, 2)->default(0.00);
            $table->double('total_withdraw', 8, 2)->default(0.00);
            $table->tinyInteger('is_online')->default(0);
            $table->integer('parent_store_id')->index()->nullable(); //modified
            $table->enum('vehicle_type', ['bike', 'car', 'van'])->nullable();
            $table->double('application_fee', 8, 2)->default(0.00);
            $table->string('temp_code', 6)->nullable();
            $table->text('referral_code')->index()->nullable(); //modified
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
        Schema::dropIfExists('users');
    }
}
