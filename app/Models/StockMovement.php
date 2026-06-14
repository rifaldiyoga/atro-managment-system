<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
  protected $table = 'stocklog';

  protected $fillable = [
    'trxdate',
    'trxno',
    'movement_type',
    'source_type',
    'source_id',
    'source_detail_id',
    'item_id',
    'wh_id',
    'qty_in',
    'qty_out',
    'unit_cost',
    'total_cost',
    'note',
    'is_reversal',
  ];

  protected $casts = [
    'trxdate' => 'datetime',
    'qty_in' => 'decimal:4',
    'qty_out' => 'decimal:4',
    'unit_cost' => 'decimal:4',
    'total_cost' => 'decimal:4',
    'is_reversal' => 'boolean',
  ];

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }
}
