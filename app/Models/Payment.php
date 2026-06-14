<?php

namespace App\Models;

use App\Helpers\TransactionHelper;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  protected $table = 'pay';

  protected $fillable = [
    'trxno',
    'trxdate',
    'type',
    'status',
    'bp_id',
    'cash_acc_id',
    'source_type',
    'source_id',
    'amount',
    'note',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'trxdate' => 'datetime',
    'amount' => 'decimal:4',
  ];

  protected static function booted()
  {
    static::saving(function ($model) {
      if (empty($model->trxno) || $model->trxno === 'AUTO') {
        $prefix = $model->type === 'OUT' ? 'PAYOUT' : 'PAYIN';
        $model->trxno = TransactionHelper::generateTrxNo(static::class, $prefix, $model->trxdate);
      }
    });
  }

  public function details()
  {
    return $this->hasMany(PaymentDetail::class, 'pay_id');
  }

  public function cashAccount()
  {
    return $this->belongsTo(Account::class, 'cash_acc_id');
  }
}
