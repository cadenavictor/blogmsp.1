<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function __invoke(Post $post): View
    {
        abort_unless(
            $post->status === 'published'
            && $post->published_at !== null
            && $post->published_at->lessThanOrEqualTo(now()),
            404,
        );

        $post->load(['author', 'category', 'tags']);

        return view('public.posts.show', [
            'post' => $post,
        ]);
    }
}
