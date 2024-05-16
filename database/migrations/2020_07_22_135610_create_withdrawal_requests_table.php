<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained(table:'users')->cascadeOnDelete();
            $table->float('amount');
            $table->enum('status',['pending','completed'])->default('pending');
            $table->json('bank_detail')->nullable()->default(null);
            $table->string('transaction_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('user_id');
            $table->index('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
}
