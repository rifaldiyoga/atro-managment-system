<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
  protected $table = 'jnld';
  protected $primaryKey = null;
  public $incrementing = false;
  public $timestamps = false;

  protected $fillable = [
    'jnl_id',
    'dno',
    'acc_id',
    'dk',
    'debit',
    'credit',
    'amount',
    'amountforex',
    'dnote',
    'prj_id',
    'dept_id',
  ];

  protected $casts = [
    'debit' => 'decimal:4',
    'credit' => 'decimal:4',
    'amount' => 'decimal:4',
    'amountforex' => 'decimal:4',
  ];

  public function account()
  {
    return $this->belongsTo(Account::class, 'acc_id');
  }
}
