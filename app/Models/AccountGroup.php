<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountGroup extends Model
{
  protected $table = 'accgrp';

  protected $fillable = ['code', 'name', 'type', 'active'];

  protected $casts = ['active' => 'boolean'];
}
