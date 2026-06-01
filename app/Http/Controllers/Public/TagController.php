<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    public function __invoke(Tag $tag): View
    {
        $posts = $tag->posts()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(10);

        return view('public.index', [
            'title' => "#{$tag->name}",
            'subtitle' => $tag->description,
            'posts' => $posts,
        ]);
    }
}
