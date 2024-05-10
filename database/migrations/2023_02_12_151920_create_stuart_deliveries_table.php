<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStuartDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stuart_deliveries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('order_id')->constrained(table:'orders')->cascadeOnDelete();
            $table->bigInteger('job_id')->comment('Stuart Job id');
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
        Schema::dropIfExists('stuart_deliveries');
    }
}
