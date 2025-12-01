<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id',
        'type',
        'quantity',
        'date',
        'is_flexible_date',
        'description',
    ];

    /**
     * Get the donor that owns this donation
     */
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }
}
