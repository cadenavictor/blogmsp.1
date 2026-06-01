<?php

use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TaxonomyController;
use Illuminate\Support\Facades\Route;

// Public OpenAPI document (discovery for agents) — no key required.
Route::get('/openapi.yaml', function () {
    $path = base_path('docs/api/openapi.yaml');

    abort_unless(is_file($path), 404);

    return response()->file($path, ['Content-Type' => 'application/yaml; charset=UTF-8']);
})->name('api.openapi');

/*
|--------------------------------------------------------------------------
| API routes (consumed by the Codex integration)
|--------------------------------------------------------------------------
|
| All routes are protected by the `api.key` middleware (static key sent via
| the `X-Api-Key` header or `Authorization: Bearer`). Responses are JSON.
|
*/

Route::middleware('api.key')->group(function (): void {
    Route::get('/ping', fn () => response()->json([
        'ok' => true,
        'service' => config('app.name'),
        'endpoints' => [
            'GET /api/news/digest?monitor=slug — temas do blog + noticias',
            'GET /api/news?q=keywords|?monitor=slug',
            'GET /api/news/monitors',
            'GET /api/categories',
            'GET /api/tags',
            'GET /api/posts',
            'GET /api/posts/{slug}',
            'POST /api/posts',
        ],
    ]));

    Route::get('/news/digest', [NewsController::class, 'digest'])->name('api.news.digest');
    Route::get('/news', [NewsController::class, 'index'])->name('api.news.index');
    Route::get('/news/monitors', [NewsController::class, 'monitors'])->name('api.news.monitors');

    Route::get('/categories', [TaxonomyController::class, 'categories'])->name('api.categories');
    Route::get('/tags', [TaxonomyController::class, 'tags'])->name('api.tags');

    Route::get('/posts', [PostController::class, 'index'])->name('api.posts.index');
    Route::post('/posts', [PostController::class, 'store'])->name('api.posts.store');
    Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('api.posts.show');
});
