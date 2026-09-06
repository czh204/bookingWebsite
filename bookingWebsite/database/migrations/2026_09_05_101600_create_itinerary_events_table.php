<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One dated entry on the planner calendar.
     *
     * `source` is what splits the two things the page shows side by side:
     *   'ai'      - suggested by the planner, not booked or paid for
     *   'booking' - backed by a real confirmed trip (trip_id is set)
     *
     * Keeping both in one table means the calendar can render a day's
     * dots and the day panel from a single query, rather than merging
     * two differently-shaped sets at request time.
     */
    public function up(): void
    {
        Schema::create('itinerary_events', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Null for AI suggestions — they exist before anything is booked.
            $table->foreignId('trip_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('source')->default('ai');
            $table->string('category')->nullable();
            $table->string('title');
            $table->string('location')->nullable();
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // The calendar always queries a user's events within a month.
            $table->index(['user_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itinerary_events');
    }
};
