<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'reference',
    'status',
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
    protected $casts = [
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
