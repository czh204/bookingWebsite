<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flight_fares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->string('fare_class');
            $table->string('badge')->nullable();
            $table->decimal('price', 8, 2);
            $table->unsignedInteger('checked_bag_kg')->nullable();
            $table->string('carry_on')->nullable();
            $table->string('seat_info')->nullable();
            $table->json('perks')->nullable();
            $table->boolean('refundable')->default(false);
            $table->decimal('change_fee_from', 8, 2)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['flight_id', 'fare_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_fares');
    }
};
