<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'reference',
    'status',
    'refunded_at',
    'subtotal',
    'discount',
    'service_fee',
    'tax',
    'total',
    'promo_code',
    'payment_method',
    'payment_brand',
    'card_last4',
    'customer_name',
    'customer_email',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_zip',
])]
class Order extends Model
{
    /** Hours a refund takes to clear, as quoted on the FAQ page. */
    public const REFUND_PROCESSING_HOURS = 48;

    protected $casts = [
        'refunded_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** The calendar entries generated for this order — see BookingItinerary. */
    public function events(): HasMany
    {
        return $this->hasMany(ItineraryEvent::class)->orderBy('event_date')->orderBy('start_time');
    }

    /**
     * The last day this booking is actually travelled on — the date that
     * decides whether it belongs under "My Bookings" or "Past Bookings".
     *
     * The latest date is used, not the earliest: a trip with a flight on
     * the 3rd and a hotel through the 9th is still in progress on the 5th
     * and shouldn't be filed away as finished.
     *
     * Falls back through the calendar entries and finally the purchase
     * date, so an order that somehow carries no dates at all still sorts
     * somewhere sensible instead of being treated as eternally upcoming.
     */
    public function travelDate(): CarbonImmutable
    {
        $dates = $this->items
            ->pluck('booking_date')
            ->merge($this->events->pluck('event_date'))
            ->filter();

        $latest = $dates->max();

        return CarbonImmutable::parse($latest ?? $this->created_at)->startOfDay();
    }

    /** True once the travel date is behind us — yesterday and earlier. */
    public function isPast(): bool
    {
        return $this->travelDate()->lt(CarbonImmutable::today());
    }

    /**
     * Only a confirmed booking that hasn't been travelled yet can be
     * refunded. A past booking has already been used, and one that is
     * cancelled or refunded has been settled once already — refunding it
     * twice would be a double payout.
     */
    public function isRefundable(): bool
    {
        return $this->status === 'confirmed' && ! $this->isPast();
    }

    /** When the refund should have cleared, for the notice on screen. */
    public function refundDueAt(): ?CarbonImmutable
    {
        return $this->refunded_at
            ? CarbonImmutable::parse($this->refunded_at)->addHours(self::REFUND_PROCESSING_HOURS)
            : null;
    }

    /**
     * Customer-facing booking reference, e.g. VYG-7K2M9QX4.
     * Uppercase and unambiguous enough to be read out over the phone.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'VYG-'.strtoupper(Str::random(8));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function getPaymentLabelAttribute(): string
    {
        if ($this->payment_method === 'card') {
            return trim(($this->payment_brand ?? 'Card').' •••• '.$this->card_last4);
        }

        return $this->payment_brand ?? 'E-Wallet';
    }
}
