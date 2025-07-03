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
        // Mengubah nama tabel dari 'salesmen' menjadi 'salesman'
        Schema::create('salesman', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('phone')->nullable();

            // Memperbarui foreign key agar terhubung ke tabel 'salesman_groups'
            // dan mengubah nama kolom agar lebih jelas
            $table->foreignId('salesman_group_id')->nullable()->constrained('salesman_groups')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesman');
    }
};
