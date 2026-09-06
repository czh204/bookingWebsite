<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * The e-wallets this site accepts.
     *
     * `qr` is the file each wallet's QR code should live at, relative to
     * public/. Drop an image in at that path and the checkout renders it;
     * until then the page shows a placeholder frame of the same size, so
     * the layout doesn't shift when the real images arrive.
     */
    public const WALLETS = [
        'tng' => ['name' => 'TnG eWallet', 'tagline' => 'Touch \'n Go', 'icon' => 'bi-wallet2', 'qr' => 'images/wallets/tng-qr.png'],
        'boost' => ['name' => 'Boost', 'tagline' => 'Boost eWallet', 'icon' => 'bi-lightning-charge', 'qr' => 'images/wallets/boost-qr.png'],
        'duitnow' => ['name' => 'DuitNow', 'tagline' => 'DuitNow QR', 'icon' => 'bi-qr-code', 'qr' => 'images/wallets/duitnow-qr.png'],
    ];

    public function __construct(protected Cart $cart) {}

    public function show()
    {
        // Nothing to pay for — bounce back rather than render an empty form.
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('cart.checkout', [
            'lines' => $this->cart->lines(),
            'totals' => $this->cart->totals(),
            'promoCode' => $this->cart->promoCode(),
            'wallets' => self::WALLETS,
        ]);
    }

    public function pay(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $method = $request->input('payment_method');

        $data = $request->validate($this->rulesFor($method), [
            'card_number.*' => 'Please enter a valid card number.',
            'card_expiry.*' => 'Enter the expiry as MM/YY.',
            'wallet.required_if' => 'Choose which e-wallet you want to pay with.',
        ]);

        // This is a mock payment: there is no gateway to decline it, so a
        // well-formed submission always succeeds. 
        $order = $this->createOrder($data, $method);

        $this->cart->clear();

        // Redirect rather than render, so a refresh on the confirmation
        // page can't replay the payment.
        return redirect()->route('checkout.confirmation', $order->reference);
    }

    public function confirmation(string $reference)
    {
        // Scoped to the signed-in user, not just the reference. A booking
        // reference is short and guessable, and the confirmation shows an
        // email and billing address — so owning the order is what grants
        // access, and someone else's reference 404s.
        $order = Order::with('items')
            ->where('reference', $reference)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('cart.confirmation', ['order' => $order]);
    }

    protected function rulesFor(?string $method): array
    {
        $shared = [
            'payment_method' => ['required', Rule::in(['card', 'ewallet'])],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:191'],
        ];

        if ($method === 'ewallet') {
            return $shared + [
                'wallet' => ['required_if:payment_method,ewallet', Rule::in(array_keys(self::WALLETS))],
            ];
        }

        return $shared + [
            // These must use array syntax, not the "a|b|c" string form: an
            // alternation `|` inside a regex would be read as a rule
            // separator and split the pattern in half.
            //
            // Spaces are stripped before the digit check, so "4242 4242
            // 4242 4242" is accepted the way it's printed on the card.
            'card_number' => ['required', 'string', 'regex:/^[0-9 ]{13,25}$/'],
            'card_holder' => ['required', 'string', 'max:120'],
            'card_expiry' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\s*\/\s*[0-9]{2}$/'],
            'card_cvv' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
            'billing_address' => ['required', 'string', 'max:191'],
            'billing_city' => ['required', 'string', 'max:100'],
            'billing_state' => ['required', 'string', 'max:100'],
            'billing_zip' => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * Writes the order and its lines in one transaction, so a failure
     * halfway through can't leave an order with missing items.
     */
    protected function createOrder(array $data, string $method): Order
    {
        $totals = $this->cart->totals();
        $lines = $this->cart->lines();

        $paymentDetails = $method === 'card'
            ? [
                'payment_brand' => $this->cardBrand($data['card_number']),
                // Only the last four are kept. The number and CVV are
                // used for validation above and then dropped on the floor.
                'card_last4' => substr(preg_replace('/\D/', '', $data['card_number']), -4),
                'billing_address' => $data['billing_address'],
                'billing_city' => $data['billing_city'],
                'billing_state' => $data['billing_state'],
                'billing_zip' => $data['billing_zip'],
            ]
            : ['payment_brand' => self::WALLETS[$data['wallet']]['name']];

        return DB::transaction(function () use ($data, $method, $totals, $lines, $paymentDetails) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'reference' => Order::generateReference(),
                'status' => 'confirmed',
                'subtotal' => $totals->subtotal,
                'discount' => $totals->discount,
                'service_fee' => $totals->service_fee,
                'tax' => $totals->tax,
                'total' => $totals->total,
                'promo_code' => $this->cart->promoCode(),
                'payment_method' => $method,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
            ] + $paymentDetails);

            foreach ($lines as $line) {
                $order->items()->create([
                    'type' => $line->type,
                    'item_id' => $line->item_id,
                    'option_key' => $line->option_key,
                    'title' => $line->title,
                    'subtitle' => $line->subtitle,
                    'meta' => $line->meta,
                    'unit_price' => $line->unit_price,
                    'quantity' => $line->quantity,
                    'line_total' => round($line->unit_price * $line->quantity, 2),
                ]);
            }

            return $order;
        });
    }

    /** Brand from the leading digits — the same prefixes issuers use. */
    protected function cardBrand(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        return match (true) {
            str_starts_with($digits, '4') => 'Visa',
            (bool) preg_match('/^5[1-5]/', $digits) => 'Mastercard',
            (bool) preg_match('/^3[47]/', $digits) => 'Amex',
            default => 'Card',
        };
    }
}
