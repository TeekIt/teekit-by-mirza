<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add minimum threshold column to van_products table
     */
    public function up(): void
    {
        Schema::table('van_products', function (Blueprint $table) {
            $table->integer('min_threshold')
                  ->default(10)
                  ->after('quantity')
                  ->comment('Minimum stock threshold for inventory alerts');
        });
    }

    /**
     * Reverse the changes
     */
    public function down(): void
    {
        Schema::table('van_products', function (Blueprint $table) {
            $table->dropColumn('min_threshold');
        });
    }
};