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
        Schema::create('van_operative_product_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('van_id')->constrained(table: 'vans')->cascadeOnDelete();
            $table->foreignId('van_product_id')->constrained(table: 'van_products')->cascadeOnDelete();
            $table->integer('quantity_used');
            $table->string('job_reference');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_operative_product_usages');
    }
};
