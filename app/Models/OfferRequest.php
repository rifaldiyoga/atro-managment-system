<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'offer_number',
        'date',
        'phd',
        'rfq_number',
        'rfq_duration'
    ];

    public function customer()
    {
        return $this->belongsTo(BusinessPartner::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OfferRequestItem::class);
    }
}
