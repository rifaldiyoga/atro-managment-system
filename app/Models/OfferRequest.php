<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OfferRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'offer_number',
        'validity',
        'payment_deadline',
        'rfq_number',
        'rfq_duration',
        'notes',
        'trx_no',
        'salesman_id',
    ];

    protected $casts = [
        'attachment' => 'array',
        'trxdate' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($offerRequest) {
            // Check if trx_no is already set to avoid overwriting it
            if (empty($offerRequest->trx_no)) {
                // Corrected to assign to trx_no instead of ph_no
                $offerRequest->ph_no = static::generateTrxNo();
            }
        });
    }

    /**
     * Generate a unique, formatted transaction number.
     * Format: [Increment]/PH/AIG/[Month]/[Year]
     * The increment number resets at the beginning of each month.
     *
     * @return string
     */
    public static function generateTrxNo(): string
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->format('m');
        $prefix = 'PH/AIG';

        // Find the last offer request created in the current month and year
        $lastOffer = self::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->latest('id') // Use the latest ID to be certain
            ->first();

        $increment = 1; // Default to 1 for the first entry of the month

        if ($lastOffer && $lastOffer->trx_no) {
            // If a previous record exists for this month, parse its trx_no
            $parts = explode('/', $lastOffer->trx_no);
            $lastIncrement = (int) $parts[0];
            $increment = $lastIncrement + 1;
        }

        // Format the new transaction number with a zero-padded increment
        return sprintf('%03d/%s/%s/%d', $increment, $prefix, $currentMonth, $currentYear);
    }


    public function customer()
    {
        return $this->belongsTo(BusinessPartner::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(OfferRequestItem::class);
    }

    public function salesman()
    {
        return $this->belongsTo(SalesmanGroup::class);
    }
}
