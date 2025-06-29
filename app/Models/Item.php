<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'code',
        'name',
        'unit',
        'price',
        'description',
        'is_active',
        'photo_url',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
