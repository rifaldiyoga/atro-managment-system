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
        // Menambahkan kolom salesman_id ke tabel offer_requests
        Schema::table('offer_requests', function (Blueprint $table) {
            $table->foreignId('salesman_id')->nullable()->after('customer_id')->constrained('salesman')->onDelete('set null');
        });

        // Menambahkan kolom salesman_id ke tabel orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('salesman_id')->nullable()->after('customer_id')->constrained('salesman')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus kolom salesman_id dari tabel offer_requests
        Schema::table('offer_requests', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->dropColumn('salesman_id');
        });

        // Menghapus kolom salesman_id dari tabel orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['salesman_id']);
            $table->dropColumn('salesman_id');
        });
    }
};
