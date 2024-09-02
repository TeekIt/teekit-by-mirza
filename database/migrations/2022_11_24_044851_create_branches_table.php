<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('parent_seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->foreignId('child_seller_id')->constrained(table:'users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            /**
             * Indexes
             */
            $table->index('parent_seller_id');
            $table->index('child_seller_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branches');
    }
}