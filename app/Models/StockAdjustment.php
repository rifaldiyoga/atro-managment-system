<?php

namespace App\Models;

use App\Helpers\TransactionHelper;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
  protected $table = 'sadj';

  protected $fillable = ['trxno', 'trxdate', 'status', 'note', 'created_by', 'updated_by'];

  protected $casts = ['trxdate' => 'datetime'];

  protected static function booted()
  {
    static::saving(function ($model) {
      if (empty($model->trxno) || $model->trxno === 'AUTO') {
        $model->trxno = TransactionHelper::generateTrxNo(static::class, 'SADJ', $model->trxdate);
      }
    });
  }

  public function details()
  {
    return $this->hasMany(StockAdjustmentDetail::class, 'sadj_id');
  }
}
