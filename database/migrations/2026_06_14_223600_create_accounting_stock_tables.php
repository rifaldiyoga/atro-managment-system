<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('accgrp', function (Blueprint $table) {
      $table->id();
      $table->string('code', 25)->unique();
      $table->string('name', 125);
      $table->string('type', 25);
      $table->boolean('active')->default(true);
      $table->timestampsTz();
    });

    Schema::create('acc', function (Blueprint $table) {
      $table->id();
      $table->string('code', 25)->unique();
      $table->string('name', 125);
      $table->foreignId('accgrp_id')->nullable()->constrained('accgrp')->nullOnDelete();
      $table->string('type', 25);
      $table->string('normal_balance', 10);
      $table->boolean('is_cash')->default(false);
      $table->boolean('active')->default(true);
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestampsTz();
    });

    Schema::create('defa', function (Blueprint $table) {
      $table->id();
      $table->string('key', 75)->unique();
      $table->string('value')->nullable();
      $table->text('note')->nullable();
      $table->timestampsTz();
    });

    Schema::create('jnl', function (Blueprint $table) {
      $table->id();
      $table->string('trxno')->unique();
      $table->timestampTz('trxdate');
      $table->unsignedBigInteger('branch_id')->nullable();
      $table->unsignedBigInteger('emp_id')->nullable();
      $table->string('reftype', 25)->nullable();
      $table->unsignedBigInteger('refid')->nullable();
      $table->string('trxtype', 25)->nullable();
      $table->unsignedInteger('version')->nullable();
      $table->boolean('isdraft')->default(true);
      $table->unsignedInteger('printcount')->nullable();
      $table->boolean('isvoid')->default(false);
      $table->string('status', 25)->nullable();
      $table->text('note')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestampTz('created_at')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestampTz('updated_at')->nullable();
      $table->boolean('isautogen')->default(false);
      $table->boolean('ismemorized')->default(false);
      $table->text('memorizednote')->nullable();
      $table->boolean('isrecurring')->default(false);
      $table->unsignedBigInteger('recur_id')->nullable();
      $table->unsignedInteger('recur_dno')->nullable();
      $table->decimal('total', 19, 4)->default(0);
      $table->unsignedBigInteger('crc_id')->default(1);
      $table->decimal('excrate', 19, 4)->default(1);
      $table->decimal('fisrate', 19, 4)->default(1);
      $table->string('reserved_var1')->nullable();
      $table->string('reserved_var2')->nullable();
      $table->string('reserved_var3')->nullable();
      $table->integer('reserved_int1')->nullable();
      $table->integer('reserved_int2')->nullable();
      $table->integer('reserved_int3')->nullable();
      $table->decimal('reserved_num1', 19, 4)->nullable();
      $table->decimal('reserved_num2', 19, 4)->nullable();
      $table->decimal('reserved_num3', 19, 4)->nullable();
      $table->index(['reftype', 'refid']);
    });

    Schema::create('jnld', function (Blueprint $table) {
      $table->unsignedBigInteger('jnl_id');
      $table->unsignedInteger('dno')->default(1);
      $table->foreignId('acc_id')->constrained('acc')->restrictOnDelete();
      $table->string('dk', 1);
      $table->decimal('debit', 19, 4)->default(0);
      $table->decimal('credit', 19, 4)->default(0);
      $table->decimal('amount', 19, 4)->default(0);
      $table->decimal('amountforex', 19, 4)->default(0);
      $table->text('dnote')->nullable();
      $table->unsignedBigInteger('prj_id')->nullable();
      $table->unsignedBigInteger('dept_id')->nullable();
      $table->foreign('jnl_id')->references('id')->on('jnl')->cascadeOnDelete();
      $table->index(['jnl_id', 'dno']);
    });

    Schema::create('pay', function (Blueprint $table) {
      $table->id();
      $table->string('trxno')->unique();
      $table->timestampTz('trxdate');
      $table->string('type', 20);
      $table->string('status', 20)->default('POSTED');
      $table->unsignedBigInteger('bp_id')->nullable();
      $table->foreignId('cash_acc_id')->constrained('acc')->restrictOnDelete();
      $table->string('source_type', 50)->nullable();
      $table->unsignedBigInteger('source_id')->nullable();
      $table->decimal('amount', 19, 4)->default(0);
      $table->text('note')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestampsTz();
    });

    Schema::create('payd', function (Blueprint $table) {
      $table->id();
      $table->foreignId('pay_id')->constrained('pay')->cascadeOnDelete();
      $table->unsignedInteger('dno')->default(1);
      $table->string('source_type', 50);
      $table->unsignedBigInteger('source_id');
      $table->decimal('amount', 19, 4)->default(0);
      $table->text('note')->nullable();
      $table->timestampsTz();
    });

    Schema::create('stockbal', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('item_id');
      $table->unsignedBigInteger('wh_id')->nullable();
      $table->decimal('qty', 19, 4)->default(0);
      $table->decimal('avg_cost', 19, 4)->default(0);
      $table->decimal('total_cost', 19, 4)->default(0);
      $table->timestampsTz();
      $table->unique(['item_id', 'wh_id']);
    });

    Schema::create('stocklog', function (Blueprint $table) {
      $table->id();
      $table->timestampTz('trxdate');
      $table->string('trxno');
      $table->string('movement_type', 10);
      $table->string('source_type', 50);
      $table->unsignedBigInteger('source_id');
      $table->unsignedBigInteger('source_detail_id')->nullable();
      $table->unsignedBigInteger('item_id');
      $table->unsignedBigInteger('wh_id')->nullable();
      $table->decimal('qty_in', 19, 4)->default(0);
      $table->decimal('qty_out', 19, 4)->default(0);
      $table->decimal('unit_cost', 19, 4)->default(0);
      $table->decimal('total_cost', 19, 4)->default(0);
      $table->text('note')->nullable();
      $table->boolean('is_reversal')->default(false);
      $table->timestampsTz();
      $table->index(['source_type', 'source_id']);
    });

    Schema::create('sadj', function (Blueprint $table) {
      $table->id();
      $table->string('trxno')->unique();
      $table->timestampTz('trxdate');
      $table->string('status', 20)->default('POSTED');
      $table->text('note')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestampsTz();
    });

    Schema::create('sadjd', function (Blueprint $table) {
      $table->id();
      $table->foreignId('sadj_id')->constrained('sadj')->cascadeOnDelete();
      $table->unsignedInteger('dno')->default(1);
      $table->unsignedBigInteger('item_id');
      $table->unsignedBigInteger('wh_id')->nullable();
      $table->string('movement_type', 10);
      $table->decimal('qty', 19, 4)->default(0);
      $table->decimal('unit_cost', 19, 4)->default(0);
      $table->text('note')->nullable();
      $table->timestampsTz();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('sadjd');
    Schema::dropIfExists('sadj');
    Schema::dropIfExists('stocklog');
    Schema::dropIfExists('stockbal');
    Schema::dropIfExists('payd');
    Schema::dropIfExists('pay');
    Schema::dropIfExists('jnld');
    Schema::dropIfExists('jnl');
    Schema::dropIfExists('defa');
    Schema::dropIfExists('acc');
    Schema::dropIfExists('accgrp');
  }
};
