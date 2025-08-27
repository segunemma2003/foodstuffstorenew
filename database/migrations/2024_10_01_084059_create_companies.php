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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('state')->nullable();
            $table->string('unique_code')->unique();
            $table->decimal('discount', 5, 2)->default(0.00);
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('company_email')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('mailing_address')->nullable();
            $table->string('fax')->nullable();
            $table->string('type_of_organization')->nullable();
            $table->string('establish_date')->nullable();
            $table->string('cac_reg')->nullable();
            $table->string('ceo_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
