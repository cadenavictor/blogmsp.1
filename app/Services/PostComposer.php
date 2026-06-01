<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PostComposer
{
    public function __construct(
        private readonly IndexNowClient $indexNow,
        private readonly HtmlSanitizer $sanitizer,
    ) {}

    /**
     * Create a complete, SEO-optimized post from a normalized payload.
     *
     * Missing SEO/GEO fields are auto-derived from the title and content so a
     * caller (e.g. the Codex integration) can send just title + content and
     * still get a publish-ready post.
     *
     * @param  array<string, mixed>  $data
     */
    public function compose(array $data, ?User $author = null): Post
    {
        $author = $author ?? $this->resolveAuthor();

        $title = trim((string) ($data['title'] ?? ''));
        $content = $this->sanitizeHtml((string) ($data['content'] ?? ''));
        $plain = $this->toPlainText($content);

        $excerpt = $this->firstNonEmpty($data['excerpt'] ?? null)
            ?? Str::limit($plain, 160);

        $status = in_array($data['status'] ?? null, ['draft', 'published', 'scheduled'], true)
            ? $data['status']
            : 'published';

        $publishedAt = $this->resolveDate($data['published_at'] ?? null);
        $scheduledAt = $this->resolveDate($data['scheduled_at'] ?? null);

        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = Carbon::now();
        }

        if ($status === 'scheduled' && $scheduledAt === null) {
            $scheduledAt = Carbon::now()->addDay();
        }

        $post = Post::create([
            'user_id' => $author->id,
            'category_id' => $this->resolveCategoryId($data['category'] ?? null),
            'title' => $title,
            'slug' => $this->uniqueSlug($this->firstNonEmpty($data['slug'] ?? null) ?? $title),
            'excerpt' => $excerpt,
            'content' => $content,
            'cover_image_path' => $this->firstNonEmpty($data['cover_image_url'] ?? $data['cover_image_path'] ?? null),
            'status' => $status,
            'featured' => (bool) ($data['featured'] ?? false),
            'published_at' => $publishedAt,
            'scheduled_at' => $scheduledAt,
            'seo_title' => Str::limit($this->firstNonEmpty($data['seo_title'] ?? null) ?? $title, 70, ''),
            'seo_description' => Str::limit($this->firstNonEmpty($data['seo_description'] ?? null) ?? $excerpt, 160, ''),
            'canonical_url' => $this->firstNonEmpty($data['canonical_url'] ?? null),
            'meta_robots' => $this->firstNonEmpty($data['meta_robots'] ?? null),
            'og_title' => $this->firstNonEmpty($data['og_title'] ?? null) ?? $title,
            'og_description' => $this->firstNonEmpty($data['og_description'] ?? null) ?? $excerpt,
            'ai_summary' => $this->firstNonEmpty($data['ai_summary'] ?? null) ?? Str::limit($plain, 280),
            'key_takeaways' => $this->cleanList($data['key_takeaways'] ?? null),
            'entities' => $this->cleanList($data['entities'] ?? null),
            'faq_items' => $this->cleanFaq($data['faq_items'] ?? null),
        ]);

        $tagIds = $this->resolveTagIds($data['tags'] ?? []);

        if ($tagIds !== []) {
            $post->tags()->sync($tagIds);
        }

        $this->maybeSubmitIndexNow($post);

        return $post->fresh(['category', 'tags', 'author']) ?? $post;
    }

    private function resolveAuthor(): User
    {
        $author = User::query()->where('is_admin', true)->oldest('id')->first()
            ?? User::query()->oldest('id')->first();

        if ($author === null) {
            throw new RuntimeException('Nenhum usuario disponivel para ser autor do post.');
        }

        return $author;
    }

    private function resolveCategoryId(mixed $category): int
    {
        if (is_int($category) || (is_string($category) && ctype_digit($category))) {
            $found = Category::find((int) $category);

            if ($found !== null) {
                return $found->id;
            }
        }

        $name = is_string($category) ? trim($category) : '';

        if ($name === '') {
            $name = 'Geral';
        }

        $slug = Str::slug($name);

        $existing = Category::where('slug', $slug)->orWhere('name', $name)->first();

        if ($existing !== null) {
            return $existing->id;
        }

        return Category::create([
            'name' => $name,
            'slug' => $slug,
        ])->id;
    }

    /**
     * @return array<int, int>
     */
    private function resolveTagIds(mixed $tags): array
    {
        if (! is_array($tags)) {
            return [];
        }

        $ids = [];

        foreach ($tags as $tag) {
            if (is_int($tag) || (is_string($tag) && ctype_digit($tag))) {
                if (Tag::whereKey((int) $tag)->exists()) {
                    $ids[] = (int) $tag;

                    continue;
                }
            }

            $name = is_string($tag) ? trim($tag) : '';

            if ($name === '') {
                continue;
            }

            $model = Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );

            $ids[] = $model->id;
        }

        return array_values(array_unique($ids));
    }

    private function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'post';
        $slug = $base;
        $suffix = 2;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function maybeSubmitIndexNow(Post $post): void
    {
        if ($post->status !== 'published') {
            return;
        }

        if ($post->published_at !== null && $post->published_at->isFuture()) {
            return;
        }

        try {
            $this->indexNow->submit([route('posts.show', $post)]);
        } catch (Throwable) {
            // Index submission is best-effort and must not block publishing.
        }
    }

    private function sanitizeHtml(string $html): string
    {
        return $this->sanitizer->clean($html);
    }

    private function toPlainText(string $html): string
    {
        $text = strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    private function firstNonEmpty(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, string>|null
     */
    private function cleanList(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $items = array_values(array_filter(array_map(
            fn ($item): string => is_string($item) ? trim($item) : '',
            $value,
        ), fn (string $item): bool => $item !== ''));

        return $items === [] ? null : $items;
    }

    /**
     * @return array<int, array{question: string, answer: string}>|null
     */
    private function cleanFaq(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $question = trim((string) ($entry['question'] ?? ''));
            $answer = trim((string) ($entry['answer'] ?? ''));

            if ($question !== '' && $answer !== '') {
                $items[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $items === [] ? null : $items;
    }

    private function resolveDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
