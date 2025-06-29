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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->default('AUTO');
            $table->string('name');
            // $table->string('unit_1')->default('PCS');
            // $table->string('barcode_1')->nullable();

            // Default units
            $table->string('unit')->default('PCS');

            // Pricing
            $table->decimal('price', 15, 2)->default(0.00); // Add price field here

            $table->text('description')->nullable();

            // Flags
            $table->boolean('is_active')->default(true);

            $table->longText('photo_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
