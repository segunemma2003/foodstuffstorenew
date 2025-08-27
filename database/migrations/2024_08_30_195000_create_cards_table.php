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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('card_number'); // Masked card number (e.g., '**** **** **** 1234')
            $table->string('card_holder_name'); // Name on the card
            $table->string('expiry_month'); // Expiry month (e.g., '12')
            $table->string('expiry_year'); // Expiry year (e.g., '2025')
            $table->string('cvv'); // Encrypted CVV code
            $table->string('card_type')->nullable(); // Card type (e.g., Visa, MasterCard)
            $table->boolean('is_default')->default(false); // If this card is the default payment method
            $table->softDeletes(); // Add SoftDeletes column
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
