<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'selling_price',
        'purchase_price',
        'supplier_id',
        'notes',
        'status',
        'discount',
        'dinego',
    ];

    protected $casts = [
        'selling_price' => 'float',
        'purchase_price' => 'float',
        'discount' => 'float',
        'dinego' => 'boolean',
    ];

    // Relationships

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function supplier()
    {
        return $this->belongsTo(BusinessPartner::class, 'supplier_id');
    }
}
