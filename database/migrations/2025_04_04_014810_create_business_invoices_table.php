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
        Schema::create('business_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_order_id')->constrained('business_orders');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('file_path');
            $table->enum('status', ['generated', 'sent', 'paid'])->default('generated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_invoices');
    }
};
