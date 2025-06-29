<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'phd',
        'ph_no',
        'po_no',
        'customer_id',
        'trxdate',
        'rfq_number',
        'rfq_duration',
    ];

    protected $casts = [
        'trxdate' => 'date',
    ];

    // Relationships

    public function customer()
    {
        return $this->belongsTo(BusinessPartner::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
