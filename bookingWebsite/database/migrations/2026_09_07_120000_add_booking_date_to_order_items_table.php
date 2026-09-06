<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The date the customer is actually booking for — the travel day, not
     * the day they paid.
     *
     * Until now the cart recorded only *what* was bought, so the planner
     * calendar had to guess: flights used the schedule's departure_date
     * and hotels/attractions fell back to the order date. This column is
     * what the checkout collects, so the calendar can stop guessing.
     *
     * Nullable only so existing rows survive the change; they're
     * backfilled below and everything written afterwards has a value.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->after('option_key');
        });

        // Existing lines predate the date picker. The order date is the
        // only date they carry, which is exactly what the calendar was
        // already assuming for them.
        DB::statement('
            UPDATE order_items
            JOIN orders ON orders.id = order_items.order_id
            SET order_items.booking_date = DATE(orders.created_at)
            WHERE order_items.booking_date IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('booking_date');
        });
    }
};
