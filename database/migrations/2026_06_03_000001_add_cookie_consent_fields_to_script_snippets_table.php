<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('script_snippets', function (Blueprint $table): void {
            $table->boolean('requires_consent')->default(false)->after('is_active')->index();
            $table->string('cookie_category')->default('essential')->after('requires_consent')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('script_snippets', function (Blueprint $table): void {
            $table->dropColumn(['requires_consent', 'cookie_category']);
        });
    }
};
