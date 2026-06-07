<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');

        if (app()->isProduction() && ($adminPassword === null || $adminPassword === 'password')) {
            throw new RuntimeException('ADMIN_PASSWORD must be set to a secure value in production.');
        }

        $admin = User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@blogmsp.local'),
        ], [
            'name' => env('ADMIN_NAME', 'Administrador'),
            'password' => Hash::make($adminPassword ?? 'password'),
        ]);

        $admin->forceFill(['is_admin' => true])->save();

        SiteSetting::query()->firstOrCreate([], [
            'site_name' => 'Melhores de São Paulo',
            'tagline' => 'Curadoria independente de empresas, serviços e experiências em São Paulo.',
            'home_title' => 'Melhores de São Paulo',
            'home_meta_description' => 'Reviews, guias e notícias para comparar empresas, serviços e experiências na cidade de São Paulo com critérios editoriais claros.',
            'default_meta_description' => 'Melhores de São Paulo reúne análises independentes, guias práticos e notícias sobre empresas e serviços da capital paulista.',
            'organization_name' => 'Melhores de São Paulo',
            'organization_description' => 'Blog editorial independente sobre empresas, serviços, bairros e experiências relevantes na cidade de São Paulo.',
            'locale' => 'pt_BR',
            'language' => 'pt-BR',
            'llms_summary' => 'Melhores de São Paulo publica reviews, guias, dicas e notícias sobre empresas e serviços da cidade de São Paulo, com foco em critérios claros, contexto local e respostas úteis para mecanismos de busca e agentes de IA.',
            'allow_ai_training' => true,
            'allow_ai_search' => true,
        ]);
    }
}
