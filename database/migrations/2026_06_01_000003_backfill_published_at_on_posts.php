<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Published posts saved without a date stay hidden (the published() scope
     * requires published_at <= now()). Backfill them with their creation date.
     */
    public function up(): void
    {
        DB::table('posts')
            ->where('status', 'published')
            ->whereNull('published_at')
            ->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        // No-op: we cannot reliably distinguish backfilled rows.
    }
};
