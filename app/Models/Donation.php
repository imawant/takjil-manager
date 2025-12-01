<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_name',
        'donor_address',
        'donor_whatsapp',
        'type',
        'quantity',
        'date',
        'is_flexible_date',
        'description',
    ];
}
