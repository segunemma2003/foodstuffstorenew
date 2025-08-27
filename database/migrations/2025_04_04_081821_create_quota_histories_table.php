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
        Schema::create('quota_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_customer_id')->constrained('business_customers');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quota_histories');
    }
};
