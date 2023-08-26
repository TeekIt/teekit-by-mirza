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
            $table->increments('id');
            $table->string('name');
            $table->string('l_name')->nullable()->default(null);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable()->default(null);
            $table->string('address_1')->nullable()->default(null);
            $table->string('address_2')->nullable()->default(null);
            $table->string('postal_code')->nullable()->default(null);
            $table->string('business_name')->nullable()->default(null);
            $table->string('business_phone')->nullable()->default(null);
            $table->json('business_location')->nullable()->default(null);
            $table->json('business_hours')->nullable()->default(null);
            $table->json('bank_details')->nullable()->default(null);
            $table->text('user_img')->nullable()->default(NULL);
            $table->datetime('last_login')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(0);
            $table->rememberToken();
            $table->timestamps();
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
