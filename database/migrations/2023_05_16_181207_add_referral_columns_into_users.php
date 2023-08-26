<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferralColumnsIntoUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('referral_code')->nullable()->after('temp_code');
            // $table->tinyInteger('referral_useable')
            // ->default(0)
            // ->comment('0: Cannot be used by the user, 1: Can be used by the user')
            // ->after('referral_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_code');
            // $table->dropColumn('referral_useable');
        });
    }
}
