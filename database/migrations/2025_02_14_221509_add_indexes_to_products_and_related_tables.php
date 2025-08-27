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
        Schema::table('products', function (Blueprint $table) {
            $table->index('name'); // Index for product search
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('name'); // Index for category filtering
        });

        Schema::table('product_quantities', function (Blueprint $table) {
            $table->index(['product_id', 'location_id']); // Index for price lookup
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->index(['product_id', 'user_id', 'deleted_at']); // Index for is_favorite check
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('product_quantities', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'location_id']);
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'user_id', 'deleted_at']);
        });
    }
};
