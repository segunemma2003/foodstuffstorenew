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
        Schema::create('delivery_fare_estimates', function (Blueprint $table) {
            $table->id();
             $table->string('pickup_address');
            $table->decimal('pickup_latitude', 10, 8);
            $table->decimal('pickup_longitude', 11, 8);
            $table->string('pickup_phone');
            $table->string('pickup_email');
            $table->string('delivery_address');
            $table->decimal('delivery_latitude', 10, 8);
            $table->decimal('delivery_longitude', 11, 8);
            $table->string('delivery_phone');
            $table->string('delivery_email');
            $table->string('restaurant_name');
            $table->string('customer_name');
            $table->integer('vehicle_id');
            $table->json('kwik_response');
            $table->decimal('fare_amount', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('insurance_amount', 10, 2)->default(0);
            $table->integer('total_tasks')->default(1);
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();


             $table->index(['expires_at', 'is_used']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_fare_estimates');
    }
};
