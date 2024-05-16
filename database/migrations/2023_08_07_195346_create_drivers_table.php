<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 16)->unique();
            $table->string('password')->nullable();
            $table->text('profile_img')->nullable();
            $table->tinyInteger('vehicle_type')->nullable()->comment('1:cycle,2:bike,3:car,4:van');
            $table->string('vehicle_number', 8)->nullable();
            $table->string('area')->nullable();
            $table->decimal('lat', 11, 8)->nullable();
            $table->decimal('lon', 11, 8)->nullable();
            $table->string('account_holders_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->integer('sort_code')->nullable();
            $table->integer('account_number')->nullable();
            $table->string('driving_licence_name')->nullable();
            $table->string('dob', 16)->nullable();
            $table->string('driving_licence_number')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('email');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
    }
}
