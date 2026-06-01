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
        Schema::create('index_now_submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('url', 2048);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('index_now_submissions');
    }
};
