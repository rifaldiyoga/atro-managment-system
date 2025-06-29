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
            $table->integer('phd');
            $table->string('ph_no'); // NO PH
            $table->string('po_no'); // NO PO
            $table->foreignId('customer_id')->constrained('business_partners')->onDelete('cascade');
            $table->date('trxdate')->nullable(); // TANGGAL
            $table->string('rfq_number'); // NO RFQ
            $table->string('rfq_duration')->nullable(); // DT
            $table->string('notes')->nullable(); // Note
            $table->timestamps();
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
