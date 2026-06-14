<?php

namespace App\Models;

use App\Helpers\TransactionHelper;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
  protected $table = 'jnl';

  protected $fillable = [
    'trxno',
    'trxdate',
    'branch_id',
    'emp_id',
    'reftype',
    'refid',
    'trxtype',
    'version',
    'isdraft',
    'printcount',
    'isvoid',
    'status',
    'note',
    'created_by',
    'created_at',
    'updated_by',
    'updated_at',
    'isautogen',
    'ismemorized',
    'memorizednote',
    'isrecurring',
    'recur_id',
    'recur_dno',
    'total',
    'crc_id',
    'excrate',
    'fisrate',
    'reserved_var1',
    'reserved_var2',
    'reserved_var3',
    'reserved_int1',
    'reserved_int2',
    'reserved_int3',
    'reserved_num1',
    'reserved_num2',
    'reserved_num3',
  ];

  protected $casts = [
    'trxdate' => 'datetime',
    'isdraft' => 'boolean',
    'isvoid' => 'boolean',
    'isautogen' => 'boolean',
    'ismemorized' => 'boolean',
    'isrecurring' => 'boolean',
    'total' => 'decimal:4',
    'excrate' => 'decimal:4',
    'fisrate' => 'decimal:4',
  ];

  protected static function booted()
  {
    static::saving(function ($model) {
      if (empty($model->trxno) || $model->trxno === 'AUTO') {
        $model->trxno = TransactionHelper::generateTrxNo(static::class, 'JNL', $model->trxdate);
      }
    });
  }

  public function lines()
  {
    return $this->hasMany(JournalEntryLine::class, 'jnl_id');
  }

  public function isPosted(): bool
  {
    return !$this->isdraft && !$this->isvoid;
  }
}
