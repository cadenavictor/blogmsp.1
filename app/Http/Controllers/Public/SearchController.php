<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $posts = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($builder) use ($query): void {
                    $builder
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.index', [
            'title' => $query === '' ? 'Buscar' : "Busca por \"{$query}\"",
            'subtitle' => 'Resultados em posts publicados.',
            'posts' => $posts,
            'searchQuery' => $query,
        ]);
    }
}
