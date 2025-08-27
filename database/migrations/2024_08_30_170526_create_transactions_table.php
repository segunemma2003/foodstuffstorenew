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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('transactionable'); // Polymorphic relationship
            $table->string('unique_code', 10)->unique(); // Unique code for each transaction
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User making the transaction
            $table->string('type'); // Type of transaction (e.g., 'debit', 'credit') without using enum
            $table->string('payment_method')->nullable(); // Payment method used
            $table->decimal('amount', 8, 2); // Transaction amount
            $table->string('status')->default('pending');
            $table->string('payment_source')->nullable(); // Source of the payment
            $table->text('description')->nullable(); // Additional description or notes
            $table->softDeletes(); // Add SoftDeletes column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
