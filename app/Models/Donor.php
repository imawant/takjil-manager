<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'whatsapp',
        'address',
    ];

    /**
     * Get all donations from this donor
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }
}
