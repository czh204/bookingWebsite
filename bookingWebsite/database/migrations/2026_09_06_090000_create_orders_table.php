<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A completed checkout.
     *
     * The money columns are stored rather than recomputed: the service
     * fee, tax rate and promo values can all change later, and a past
     * order has to keep showing what was actually charged.
     *
     * Note what is NOT here — no card number, no CVV, no expiry. Only the
     * brand and last four digits are kept, which is all a receipt needs.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            // Every new order has an owner — the checkout routes are behind
            // auth middleware, so this is always populated in practice. The
            // column stays nullable for two reasons: nullOnDelete() below
            // needs it (a closed account shouldn't erase its order history,
            // which the finance side still needs), and orders placed before
            // checkout required sign-in would otherwise block the change.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 20)->unique();
            $table->string('status')->default('confirmed');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('promo_code', 32)->nullable();

            $table->string('payment_method');          // card | ewallet
            $table->string('payment_brand')->nullable(); // Visa, Boost, TnG eWallet...
            $table->string('card_last4', 4)->nullable();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('billing_address')->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_zip', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
