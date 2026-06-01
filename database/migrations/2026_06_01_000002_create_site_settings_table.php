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
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();

            // Identidade / Home
            $table->string('site_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('home_title')->nullable();
            $table->text('home_meta_description')->nullable();
            $table->text('default_meta_description')->nullable();
            $table->string('default_og_image', 2048)->nullable();
            $table->string('logo_path', 2048)->nullable();
            $table->string('favicon_path', 2048)->nullable();

            // Organizacao & Redes
            $table->string('organization_name')->nullable();
            $table->text('organization_description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('twitter_handle')->nullable();
            $table->json('social_links')->nullable();

            // Locale / GEO
            $table->string('locale')->default('pt_BR');
            $table->string('language')->default('pt-BR');
            $table->text('llms_summary')->nullable();
            $table->text('ai_policy')->nullable();
            $table->boolean('allow_ai_training')->default(true);
            $table->boolean('allow_ai_search')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
