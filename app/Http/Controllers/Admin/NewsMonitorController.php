<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsMonitorRequest;
use App\Models\NewsMonitor;
use App\Services\GoogleNewsClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsMonitorController extends Controller
{
    public function index(): View
    {
        return view('admin.news.index', [
            'monitors' => NewsMonitor::query()
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.news.create', [
            'monitor' => new NewsMonitor([
                'language' => (string) config('services.google_news.language', 'pt-BR'),
                'country' => (string) config('services.google_news.country', 'BR'),
                'max_results' => 20,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(NewsMonitorRequest $request): RedirectResponse
    {
        $monitor = NewsMonitor::create($request->validated());

        return redirect()
            ->route('admin.news.show', $monitor)
            ->with('status', 'Monitoramento criado.');
    }

    public function show(NewsMonitor $news, GoogleNewsClient $client): View
    {
        return view('admin.news.show', [
            'monitor' => $news,
            'items' => $client->fetchForMonitor($news),
        ]);
    }

    public function edit(NewsMonitor $news): View
    {
        return view('admin.news.edit', [
            'monitor' => $news,
        ]);
    }

    public function update(NewsMonitorRequest $request, NewsMonitor $news): RedirectResponse
    {
        $news->update($request->validated());

        return redirect()
            ->route('admin.news.show', $news)
            ->with('status', 'Monitoramento atualizado.');
    }

    public function destroy(NewsMonitor $news): RedirectResponse
    {
        $news->delete();

        return redirect()
            ->route('admin.news.index')
            ->with('status', 'Monitoramento removido.');
    }
}
