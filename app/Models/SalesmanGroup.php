<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesmanGroup extends Model
{
    use HasFactory;

    protected $table = 'salesman_groups';
    protected $fillable = ['name'];

    public function salesmen()
    {
        return $this->hasMany(Salesman::class, 'salesman_group_id');
    }
}
