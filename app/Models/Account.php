<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
  protected $table = 'acc';

  protected $fillable = [
    'code',
    'name',
    'accgrp_id',
    'type',
    'normal_balance',
    'is_cash',
    'active',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'active' => 'boolean',
    'is_cash' => 'boolean',
  ];

  public function group()
  {
    return $this->belongsTo(AccountGroup::class, 'accgrp_id');
  }
}
