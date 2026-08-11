<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->string('address');
            $table->unsignedTinyInteger('star_rating');
            $table->decimal('rating', 2, 1);
            $table->unsignedInteger('review_count');
            $table->string('badge')->nullable();
            $table->decimal('price_per_night', 8, 2);
            $table->text('description')->nullable();
            $table->time('check_in_time');
            $table->time('check_out_time');
            $table->string('contact_phone')->nullable();
            $table->json('amenities')->nullable();
            $table->json('policies')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
