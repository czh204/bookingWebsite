<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One purchased line per row.
     *
     * Title, subtitle and price are copied in rather than joined back to
     * flights/hotels/attractions. A receipt has to keep reading the same
     * years later, even if the fare is repriced or the attraction is
     * deleted — so `item_id` is a soft pointer, not a foreign key.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type');        // flight | hotel | attraction
            $table->unsignedBigInteger('item_id');
            $table->string('option_key');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('meta')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
