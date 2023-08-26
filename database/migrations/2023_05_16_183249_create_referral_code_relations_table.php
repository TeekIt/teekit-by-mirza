<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralCodeRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_code_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->unsignedBigInteger('user_id');          
            // $table->foreign('referred_by')->references('id')->on('users')->onDelete('cascade');  
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->tinyInteger('referral_useable')
            ->default(1)
            ->comment('0: Referral cannot be used by the user, 1: Can be used by the user');
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
        Schema::dropIfExists('referral_code_relations');
    }
}
