<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $posts = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(10);

        return view('public.index', [
            'title' => 'Blog MSP',
            'subtitle' => 'Artigos, guias e novidades publicados.',
            'posts' => $posts,
        ]);
    }
}
