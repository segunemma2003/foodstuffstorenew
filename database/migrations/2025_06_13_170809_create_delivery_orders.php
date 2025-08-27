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
        Schema::create('deliveries_orders', function (Blueprint $table) {
            $table->id();
             $table->foreignId('fare_estimate_id')->constrained('delivery_fare_estimates')->onDelete('cascade');
            $table->string('order_reference')->unique();
            $table->string('kwik_job_id');
            $table->string('kwik_job_token');
            $table->text('pickup_tracking_link');
            $table->text('delivery_tracking_link');
            $table->string('pickup_hash');
            $table->string('delivery_hash');
            $table->text('special_instructions')->nullable();
            $table->string('status')->default('created');
            $table->integer('job_status')->default(6); // UNASSIGNED
            $table->json('kwik_response');
            $table->timestamp('last_status_update')->nullable();

            $table->index('order_reference');
            $table->index('kwik_job_id');
            $table->index('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
