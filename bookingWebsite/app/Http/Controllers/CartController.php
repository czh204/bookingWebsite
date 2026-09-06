<?php

namespace App\Http\Controllers;

use App\Services\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected Cart $cart) {}

    public function index()
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'totals' => $this->cart->totals(),
            'promoCode' => $this->cart->promoCode(),
            'promoLabel' => $this->cart->promoLabel(),
        ]);
    }

    /**
     * Called by the Add to Cart / Book buttons in the flight, hotel and
     * attraction modals. The request names the item and option only —
     * Cart::add() looks the price up itself.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:flight,hotel,attraction'],
            'item_id' => ['required', 'integer', 'min:1'],
            'option_key' => ['required', 'string', 'max:50'],
            // after_or_equal:today is the no-backdating rule. Cart::add()
            // re-checks it, but validating here gives a usable message
            // instead of a generic "unavailable".
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:9'],
        ], [
            'booking_date.required' => 'Choose a date for this booking.',
            'booking_date.after_or_equal' => 'Booking dates can\'t be in the past.',
            'booking_date.date_format' => 'That date isn\'t valid.',
        ]);

        $line = $this->cart->add(
            $data['type'],
            (int) $data['item_id'],
            $data['option_key'],
            $data['booking_date'],
            (int) ($data['quantity'] ?? 1),
        );

        if (! $line) {
            return $request->expectsJson()
                ? response()->json(['message' => 'That option is no longer available.'], 422)
                : back()->with('cart_error', 'That option is no longer available.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Added to cart.',
                'count' => $this->cart->count(),
                'line' => $line,
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function destroy(string $lineId)
    {
        $this->cart->remove($lineId);

        return redirect()->route('cart.index');
    }

    public function applyPromo(Request $request)
    {
        $data = $request->validate([
            'promo_code' => 'nullable|string|max:32',
        ]);

        $code = trim((string) ($data['promo_code'] ?? ''));

        // An empty box is how you clear a code you'd already applied.
        if ($code === '') {
            $this->cart->clearPromo();

            return redirect()->route('cart.index');
        }

        if (! $this->cart->applyPromo($code)) {
            return redirect()->route('cart.index')
                ->with('promo_error', "\"{$code}\" isn't a valid promo code.");
        }

        return redirect()->route('cart.index')
            ->with('promo_success', 'Promo code applied.');
    }
}
