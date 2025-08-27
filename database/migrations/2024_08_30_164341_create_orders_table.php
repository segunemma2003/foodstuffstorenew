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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('unique_code', 6)->unique();
            $table->decimal('subtotal', 8, 2);
            $table->string('payment_method')->nullable();
            $table->decimal('tax', 8, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_status')->default('pending'); // Removed enum, handle with validation
            $table->string('status')->default('pending');         // Removed enum, handle with validation
            $table->decimal('total_amount', 8, 2);
            $table->string('delivery_address');
            $table->foreignId('delivery_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('estimated_delivery_time')->nullable();
            $table->text('delivery_issues')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
