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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // QTY
            $table->decimal('selling_price', 15, 2)->nullable(); // HARGA JUAL
            $table->decimal('purchase_price', 15, 2)->nullable(); // HARGA BELI
            $table->foreignId('supplier_id')->nullable()->constrained('business_partners')->onDelete('set null');
            $table->string('status')->nullable(); // STATUS
            $table->string('dnotes')->nullable(); // STATUS
            $table->decimal('discount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
