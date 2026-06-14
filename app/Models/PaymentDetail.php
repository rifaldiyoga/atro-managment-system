<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
  protected $table = 'payd';

  protected $fillable = ['pay_id', 'dno', 'source_type', 'source_id', 'amount', 'note'];

  protected $casts = ['amount' => 'decimal:4'];
}
