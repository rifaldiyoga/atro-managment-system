<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    use HasFactory;

    protected $table = 'salesman';
    protected $fillable = ['name', 'code', 'phone', 'salesman_group_id'];

    public function salesmanGroup()
    {
        return $this->belongsTo(SalesmanGroup::class, 'salesman_group_id');
    }
}
