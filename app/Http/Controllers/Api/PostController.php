<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostComposer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    /**
     * GET /api/posts
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->with(['category', 'tags'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = (string) $request->query('q');
                $q->where(fn ($b) => $b
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(min((int) $request->query('per_page', 15), 50));

        return PostResource::collection($posts);
    }

    /**
     * GET /api/posts/{post:slug}
     */
    public function show(Post $post): PostResource
    {
        return new PostResource($post->load(['category', 'tags', 'author']));
    }

    /**
     * POST /api/posts — create a complete, SEO-optimized post.
     */
    public function store(StorePostRequest $request, PostComposer $composer): JsonResponse
    {
        $post = $composer->compose($request->validated());

        return (new PostResource($post))
            ->additional(['message' => 'Post criado e otimizado.'])
            ->response()
            ->setStatusCode(201);
    }
}
