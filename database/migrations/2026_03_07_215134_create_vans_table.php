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
        Schema::create('vans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained(table: 'users')->cascadeOnDelete();
            $table->string('username');
            $table->string('operative');
            $table->string('number_plate')->unique();
            $table->integer('payload');
            $table->decimal('width', 2, 2);
            $table->decimal('height', 2, 2);
            $table->decimal('length', 2, 2);
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vans');
    }
};
