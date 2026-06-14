<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
  protected $table = 'stockbal';

  protected $fillable = ['item_id', 'wh_id', 'qty', 'avg_cost', 'total_cost'];

  protected $casts = [
    'qty' => 'decimal:4',
    'avg_cost' => 'decimal:4',
    'total_cost' => 'decimal:4',
  ];

  public function item()
  {
    return $this->belongsTo(Item::class, 'item_id');
  }
}
