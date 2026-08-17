<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'category',
    'city',
    'country',
    'location_label',
    'duration_hours',
    'duration_label',
    'capacity',
    'price',
    'description',
    'meeting_point',
    'included',
    'not_included',
    'what_to_bring',
    'min_age_fitness',
    'languages',
    'cancellation_policy',
])]
class Attraction extends Model
{
    protected $casts = [
        'duration_hours' => 'decimal:1',
        'price' => 'decimal:2',
        'included' => 'array',
        'not_included' => 'array',
        'what_to_bring' => 'array',
    ];

    public function timeSlots(): HasMany
    {
        return $this->hasMany(AttractionTimeSlot::class)->orderBy('sort_order');
    }
}
