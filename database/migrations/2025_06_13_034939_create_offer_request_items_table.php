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
        Schema::create('offer_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('supplier_id')->constrained('business_partners');
            $table->integer('quantity');
            $table->decimal('selling_price', 15, 2);
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('discount', 5, 2)->nullable();
            $table->text('dnotes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_request_items');
    }
};
