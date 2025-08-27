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
     if (!Schema::hasTable('bot_orders')) {


        Schema::create('bot_orders', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('slug');
            $table->boolean('status')->default(false);
            $table->decimal('total_amount',12,2)->default(0.00);
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_orders');
    }
};
