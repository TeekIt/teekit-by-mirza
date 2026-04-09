<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('operative_product_usages', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('van_id');
        $table->unsignedBigInteger('van_product_id');

        $table->integer('quantity_used');
        $table->string('job_reference');
        $table->timestamp('used_at')->nullable();

        $table->timestamps();
        $table->softDeletes();

        $table->foreign('van_id')
              ->references('id')
              ->on('vans');

        $table->foreign('van_product_id')
              ->references('id')
              ->on('van_products');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operative_product_usages');
    }
};
