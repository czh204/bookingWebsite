<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A booked trip — one row per confirmed booking. This is what the
     * planner's "My Trips" tab lists, and what the calendar's booking
     * events hang off.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            // Shown on the trip card so a booking can be quoted to support.
            $table->string('booking_reference', 20)->unique();
            $table->string('status')->default('upcoming');
            $table->decimal('total_price', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
