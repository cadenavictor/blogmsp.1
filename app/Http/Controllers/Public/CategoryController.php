<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __invoke(Category $category): View
    {
        $posts = $category->posts()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->paginate(10);

        return view('public.index', [
            'title' => $category->name,
            'subtitle' => $category->description,
            'posts' => $posts,
        ]);
    }
}
