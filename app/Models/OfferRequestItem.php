<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_request_id',
        'item_id',
        'supplier_id',
        'quantity',
        'selling_price',
        'purchase_price',
        'discount',
        'notes',
    ];

    public function offerRequest()
    {
        return $this->belongsTo(OfferRequest::class);
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
