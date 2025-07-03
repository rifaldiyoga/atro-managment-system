<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_requests', function (Blueprint $table) {
            // Menambahkan kolom validity setelah rfq_duration
            $table->string('validity')->nullable()->after('rfq_duration');

            // Menambahkan kolom payment_deadline setelah validity
            $table->string('payment_deadline')->nullable()->after('validity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offer_requests', function (Blueprint $table) {
            $table->dropColumn(['validity', 'payment_deadline']);
        });
    }
};
