<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'photo_url',
        'partner_type',
        'description',
        'is_active',
        'address',
    ];
}
