<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TaxonomyController extends Controller
{
    /**
     * GET /api/categories
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'data' => Category::query()
                ->withCount('posts')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description']),
        ]);
    }

    /**
     * GET /api/tags
     */
    public function tags(): JsonResponse
    {
        return response()->json([
            'data' => Tag::query()
                ->withCount('posts')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }
}
