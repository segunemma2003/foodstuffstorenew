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
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the size (e.g., Small, Medium, Large)
            $table->text('details')->nullable(); // Additional details about the size
            $table->string('symbol')->nullable(); // Symbol or abbreviation for the size (e.g., S, M, L)
            $table->softDeletes(); //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
