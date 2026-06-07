<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Services\SeoMetaBuilder;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(SeoMetaBuilder $seo): View
    {
        $settings = SiteSetting::current();

        $posts = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(10);

        return view('public.index', [
            'title' => $settings->home_title ?: $settings->siteName(),
            'subtitle' => $settings->tagline ?: 'Curadoria independente de empresas, serviços e experiências em São Paulo.',
            'seoMeta' => $seo->forHome(),
            'posts' => $posts,
        ]);
    }
}
