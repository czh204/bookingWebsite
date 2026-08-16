<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attractions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('city');
            $table->string('country');
            $table->string('location_label');
            $table->decimal('rating', 2, 1);
            $table->unsignedInteger('review_count');
            $table->decimal('duration_hours', 4, 1);
            $table->string('duration_label');
            $table->unsignedInteger('capacity');
            $table->decimal('price', 8, 2);
            $table->text('description')->nullable();
            $table->string('meeting_point')->nullable();
            $table->json('included')->nullable();
            $table->json('not_included')->nullable();
            $table->json('what_to_bring')->nullable();
            $table->string('min_age_fitness')->nullable();
            $table->string('languages')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attractions');
    }
};
