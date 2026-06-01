<?php

namespace App\Providers;

use App\Models\ScriptSnippet;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.public', function ($view): void {
            $scriptSnippetsByPosition = collect();

            if (Schema::hasTable('script_snippets')) {
                $scriptSnippetsByPosition = ScriptSnippet::active()
                    ->orderBy('id')
                    ->get()
                    ->groupBy('position');
            }

            $view->with('scriptSnippetsByPosition', $scriptSnippetsByPosition);
        });
    }
}
