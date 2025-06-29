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
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('notes');
        });

        Schema::table('offer_request_items', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('offer_request_items', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
