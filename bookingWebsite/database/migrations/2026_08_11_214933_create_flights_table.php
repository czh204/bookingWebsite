<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('airline_name');
            $table->string('airline_code', 3);
            $table->string('flight_number');
            $table->string('aircraft')->nullable();
            $table->string('origin_code', 3);
            $table->string('origin_city');
            $table->string('destination_code', 3);
            $table->string('destination_city');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->unsignedInteger('duration_minutes');
            $table->unsignedTinyInteger('stops')->default(0);
            $table->decimal('price', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
