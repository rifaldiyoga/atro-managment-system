<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentDetail extends Model
{
  protected $table = 'sadjd';

  protected $fillable = ['sadj_id', 'dno', 'item_id', 'wh_id', 'movement_type', 'qty', 'unit_cost', 'note'];

  protected $casts = [
    'qty' => 'decimal:4',
    'unit_cost' => 'decimal:4',
  ];
}
