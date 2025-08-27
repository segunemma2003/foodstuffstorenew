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
        Schema::create('month_employees', function (Blueprint $table) {
            $table->id();
            $table->decimal("total_bought", 18,2)->default(0.00);
            $table->decimal("total_owed", 18,2)->default(0.00);
            $table->decimal("credit_balance", 18,2)->default(0.00);
            $table->decimal("monthly_due", 18,2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('month_employees');
    }
};
