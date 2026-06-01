<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AuthorController extends Controller
{
    public function __invoke(User $user): View
    {
        abort_unless($user->posts()->published()->exists(), 404);

        $posts = $user->posts()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(10);

        return view('public.index', [
            'title' => $user->name,
            'subtitle' => $user->bio,
            'posts' => $posts,
        ]);
    }
}
