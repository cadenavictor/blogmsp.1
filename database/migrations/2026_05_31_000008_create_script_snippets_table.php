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
        Schema::create('script_snippets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->string('position')->index();
            $table->longText('content');
            $table->boolean('is_active')->default(false)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('script_snippets');
    }
};
