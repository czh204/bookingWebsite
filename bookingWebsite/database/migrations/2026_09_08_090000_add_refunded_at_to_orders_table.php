<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When a refund was requested.
     *
     * The status column already carries "refunded", but that alone can't
     * answer "when does the 48 hours run out?" — the notice shown to the
     * customer needs the moment approval was given, and updated_at is not
     * that: any later edit to the row would move it.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('refunded_at');
        });
    }
};
