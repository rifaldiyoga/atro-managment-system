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
        Schema::create('offer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('business_partners'); // customer
            $table->string('offer_number')->unique();
            $table->date('date');
            $table->string('phd');
            $table->string('ph_no');
            $table->string('rfq_number')->nullable();
            $table->string('rfq_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_requests');
    }
};
