<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsMonitor;
use App\Services\GoogleNewsClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * GET /api/news
     *
     * Consume Google News by free-form keywords (?q=) or a saved monitor (?monitor=slug|id).
     */
    public function index(Request $request, GoogleNewsClient $client): JsonResponse
    {
        $monitorRef = trim((string) $request->query('monitor', ''));
        $monitor = null;

        if ($monitorRef !== '') {
            $monitor = NewsMonitor::query()
                ->where('slug', $monitorRef)
                ->orWhere('id', ctype_digit($monitorRef) ? (int) $monitorRef : 0)
                ->first();

            if ($monitor === null) {
                return response()->json(['message' => 'Monitoramento nao encontrado.'], 404);
            }

            $items = $client->fetchForMonitor($monitor);
            $query = $monitor->keywords;
        } else {
            $query = trim((string) $request->query('q', ''));

            if ($query === '') {
                return response()->json([
                    'message' => 'Informe ?q= com as palavras-chave ou ?monitor= com o slug de um monitoramento.',
                ], 422);
            }

            $items = $client->fetch(
                $query,
                $request->query('language') ? (string) $request->query('language') : null,
                $request->query('country') ? (string) $request->query('country') : null,
                (int) $request->query('limit', 20),
            );
        }

        return response()->json([
            'query' => $query,
            'monitor' => $monitor?->slug,
            'count' => count($items),
            'data' => $items,
        ]);
    }

    /**
     * GET /api/news/monitors
     */
    public function monitors(): JsonResponse
    {
        return response()->json([
            'data' => NewsMonitor::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'keywords', 'language', 'country', 'max_results']),
        ]);
    }

    /**
     * GET /api/news/digest
     *
     * The blog's saved themes (monitors) together with their latest Google News
     * items, in a single call — the brief the Codex uses to pick a topic, write
     * and publish. Optional ?monitor=slug to scope to one theme, ?limit=N items.
     */
    public function digest(Request $request, GoogleNewsClient $client): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 5), 20));
        $monitorRef = trim((string) $request->query('monitor', ''));

        $monitors = NewsMonitor::query()
            ->active()
            ->when($monitorRef !== '', function ($query) use ($monitorRef): void {
                $query->where(function ($query) use ($monitorRef): void {
                    $query->where('slug', $monitorRef)
                        ->orWhere('id', ctype_digit($monitorRef) ? (int) $monitorRef : 0);
                });
            })
            ->orderBy('name')
            ->get();

        $data = $monitors->map(function (NewsMonitor $monitor) use ($client, $limit): array {
            $items = $client->fetch($monitor->keywords, $monitor->language, $monitor->country, $limit);

            return [
                'monitor' => [
                    'id' => $monitor->id,
                    'name' => $monitor->name,
                    'slug' => $monitor->slug,
                    'keywords' => $monitor->keywords,
                    'language' => $monitor->language,
                    'country' => $monitor->country,
                ],
                'count' => count($items),
                'items' => $items,
            ];
        })->all();

        return response()->json([
            'count' => count($data),
            'data' => $data,
        ]);
    }
}
