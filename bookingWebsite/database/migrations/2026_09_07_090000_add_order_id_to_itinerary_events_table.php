<?php

use App\Models\Order;
use App\Services\BookingItinerary;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a calendar entry back to the order that paid for it, so the
     * planner can show real bookings alongside AI-planned days.
     *
     * Nullable because the two sources of booking events differ: entries
     * belonging to a seeded trip have a trip_id and no order, entries from
     * a checkout have an order_id and no trip.
     */
    public function up(): void
    {
        Schema::table('itinerary_events', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('trip_id')
                ->constrained()
                ->cascadeOnDelete();
        });

        // Backfill: orders placed before this column existed have no
        // calendar entries yet. Reuses the same service the checkout does,
        // rather than restating the date rules here.
        $itinerary = app(BookingItinerary::class);

        Order::with('items')->whereNotNull('user_id')->each(
            fn (Order $order) => $itinerary->syncOrder($order)
        );
    }

    public function down(): void
    {
        Schema::table('itinerary_events', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
