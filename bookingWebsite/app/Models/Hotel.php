<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'city',
    'country',
    'address',
    'star_rating',
    'rating',
    'review_count',
    'badge',
    'price_per_night',
    'description',
    'check_in_time',
    'check_out_time',
    'contact_phone',
    'amenities',
    'policies',
])]
class Hotel extends Model
{
    protected $casts = [
        'rating' => 'decimal:1',
        'price_per_night' => 'decimal:2',
        'amenities' => 'array',
        'policies' => 'array',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class)->orderBy('sort_order');
    }
}
