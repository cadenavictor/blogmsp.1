<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $posts = Post::query()
            ->with(['author', 'category', 'tags'])
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($builder) use ($query): void {
                    $builder
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->query('status')))
            ->when($request->filled('category_id'), fn ($builder) => $builder->where('category_id', $request->integer('category_id')))
            ->when($request->filled('tag_id'), function ($builder) use ($request): void {
                $builder->whereHas('tags', fn ($builder) => $builder->whereKey($request->integer('tag_id')));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'filters' => [
                'q' => $query,
                'status' => $request->query('status', ''),
                'category_id' => $request->query('category_id', ''),
                'tag_id' => $request->query('tag_id', ''),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $post = new Post([
            'status' => 'draft',
            'title' => trim((string) $request->query('title', '')),
        ]);

        $sourceUrl = trim((string) $request->query('source_url', ''));

        if ($sourceUrl !== '' && filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
            $post->content = '<p><em>Fonte de referencia: <a href="'.e($sourceUrl).'" rel="noopener">'.e($sourceUrl).'</a></em></p>';
        }

        return view('admin.posts.create', $this->formData($post));
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = Post::create($this->postData($request) + [
            'user_id' => $request->user()->id,
        ]);

        $post->tags()->sync($this->tagIds($request));

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post criado.');
    }

    public function edit(Post $post): View
    {
        $post->load('tags');

        return view('admin.posts.edit', $this->formData($post));
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post->update($this->postData($request));

        if ($request->has('tag_ids') || $request->has('tags')) {
            $post->tags()->sync($this->tagIds($request));
        }

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('status', 'Post atualizado.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Post removido.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'selectedTagIds' => $post->exists ? $post->tags->pluck('id')->all() : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function postData(PostRequest $request): array
    {
        $data = $request->safe()->except(['tag_ids', 'tags']);
        $data['featured'] = $request->boolean('featured');

        if (isset($data['content'])) {
            $data['content'] = app(HtmlSanitizer::class)->clean((string) $data['content']);
        }

        return $data;
    }

    /**
     * @return array<int, int>
     */
    private function tagIds(PostRequest $request): array
    {
        return collect($request->input('tag_ids', $request->input('tags', [])))
            ->filter(fn ($tagId): bool => $tagId !== null && $tagId !== '')
            ->map(fn ($tagId): int => (int) $tagId)
            ->unique()
            ->values()
            ->all();
    }
}
