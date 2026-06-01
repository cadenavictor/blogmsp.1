<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\NewsMonitor;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusLabels = [
            'published' => 'Publicados',
            'draft' => 'Rascunhos',
            'scheduled' => 'Agendados',
        ];

        $countsByStatus = Post::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.dashboard', [
            'statusCounts' => collect($statusLabels)
                ->map(fn (string $label, string $status): array => [
                    'status' => $status,
                    'label' => $label,
                    'count' => (int) ($countsByStatus[$status] ?? 0),
                ])
                ->values(),
            'totalPosts' => Post::query()->count(),
            'totalCategories' => Category::query()->count(),
            'totalTags' => Tag::query()->count(),
            'activeMonitors' => NewsMonitor::query()->where('is_active', true)->count(),
            'featuredMonitor' => NewsMonitor::query()->where('is_active', true)->orderBy('name')->first(),
            'apiKeyConfigured' => trim((string) config('services.codex.api_key')) !== '',
            'recentPosts' => Post::query()
                ->with(['author', 'category'])
                ->latest('updated_at')
                ->limit(8)
                ->get(),
            'machineFiles' => [
                [
                    'label' => 'Sitemap',
                    'path' => '/sitemap.xml',
                    'url' => route('sitemap.index'),
                    'description' => 'Indice XML para mecanismos de busca.',
                ],
                [
                    'label' => 'Robots',
                    'path' => '/robots.txt',
                    'url' => route('robots'),
                    'description' => 'Regras de rastreamento e descoberta.',
                ],
                [
                    'label' => 'LLMs',
                    'path' => '/llms.txt',
                    'url' => route('llms'),
                    'description' => 'Resumo legivel por modelos e agentes.',
                ],
            ],
        ]);
    }
}
