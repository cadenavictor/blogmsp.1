<?php

use App\Http\Controllers\Admin\ApiReferenceController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IndexNowSubmissionController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NewsMonitorController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ScriptSnippetController as AdminScriptSnippetController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Public\AuthorController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MachineReadableFileController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\TagController;
use App\Services\IndexNowClient;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [MachineReadableFileController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/sitemap-posts.xml', [MachineReadableFileController::class, 'sitemapPosts'])->name('sitemap.posts');
Route::get('/sitemap-categories.xml', [MachineReadableFileController::class, 'sitemapCategories'])->name('sitemap.categories');
Route::get('/sitemap-tags.xml', [MachineReadableFileController::class, 'sitemapTags'])->name('sitemap.tags');
Route::get('/sitemap-pages.xml', [MachineReadableFileController::class, 'sitemapPages'])->name('sitemap.pages');
Route::get('/feed.xml', [MachineReadableFileController::class, 'feed'])->name('feed');
Route::get('/robots.txt', [MachineReadableFileController::class, 'robots'])->name('robots');
Route::get('/llms.txt', [MachineReadableFileController::class, 'llms'])->name('llms');
Route::get('/{indexNowKey}.txt', function (string $indexNowKey) {
    $key = app(IndexNowClient::class)->key();

    abort_unless(hash_equals($key, $indexNowKey), 404);

    return response($key, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->where('indexNowKey', '[A-Za-z0-9_-]{8,128}')->name('indexnow.key');

Route::get('/', HomeController::class)->name('home');
Route::get('/posts/{post:slug}', PostController::class)->name('posts.show');
Route::get('/categorias/{category:slug}', CategoryController::class)->name('categories.show');
Route::get('/tags/{tag:slug}', TagController::class)->name('tags.show');
Route::get('/autores/{user}', AuthorController::class)->name('authors.show');
Route::get('/buscar', SearchController::class)->name('search');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/admin', DashboardController::class)
    ->middleware(['auth', 'admin'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('indexnow', [IndexNowSubmissionController::class, 'index'])->name('indexnow.index');
        Route::post('indexnow', [IndexNowSubmissionController::class, 'store'])->name('indexnow.store');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::get('integracoes', [ApiReferenceController::class, 'index'])->name('integracoes');
        Route::get('configuracoes/seo', [SiteSettingController::class, 'edit'])->name('settings.seo.edit');
        Route::put('configuracoes/seo', [SiteSettingController::class, 'update'])->name('settings.seo.update');
        Route::resource('posts', AdminPostController::class)->except('show');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('tags', AdminTagController::class)->except('show');
        Route::resource('news', NewsMonitorController::class);
        Route::resource('scripts', AdminScriptSnippetController::class)->except('show');
    });
