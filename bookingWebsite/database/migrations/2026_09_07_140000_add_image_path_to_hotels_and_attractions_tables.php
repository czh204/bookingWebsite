<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where a record's photo lives.
     *
     * Either a path under public/ ("images/hotels/peninsula.jpg") or a
     * full URL ("https://…/photo.jpg"). Nullable, and null is the normal
     * state — a record without a photo falls back to the gradient
     * placeholder, so this can be filled in one row at a time.
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('badge');
        });

        Schema::table('attractions', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('attractions', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
