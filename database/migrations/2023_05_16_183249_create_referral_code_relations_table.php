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
            $table->bigIncrements('id');
            $table->foreignId('referred_by')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(table:'users')->cascadeOnDelete();
            $table->tinyInteger('referral_useable')
            ->default(1)
            ->comment('0: Referral cannot be used by the user, 1: Can be used by the user');
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
        Schema::dropIfExists('referral_code_relations');
    }
}
