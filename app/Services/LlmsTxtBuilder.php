<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LlmsTxtBuilder
{
    public function text(): string
    {
        $siteName = $this->plainText((string) config('app.name', 'Blog MSP'));

        return collect([
            "# {$siteName}",
            '',
            "Site: {$this->url('/')}",
            '',
            "Summary: {$siteName} publishes articles, guides, and updates for MSP operators and technical teams.",
            '',
            '## Key Sections',
            "- Home: {$this->url('/')}",
            ...$this->categoryLines(),
            ...$this->tagLines(),
            '',
            '## Important Posts',
            ...$this->postLines(),
            '',
        ])->implode("\n");
    }

    /**
     * @return array<int, string>
     */
    private function categoryLines(): array
    {
        return Category::query()
            ->whereHas('posts', fn (Builder $query) => $this->indexablePosts($query))
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): string => "- Category: {$this->plainText($category->name)} - {$this->url("/categorias/{$category->slug}")}")
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function tagLines(): array
    {
        return Tag::query()
            ->whereHas('posts', fn (Builder $query) => $this->indexablePosts($query))
            ->orderBy('name')
            ->get()
            ->map(fn (Tag $tag): string => "- Tag: {$this->plainText($tag->name)} - {$this->url("/tags/{$tag->slug}")}")
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function postLines(): array
    {
        return Post::query()
            ->published()
            ->where(fn (Builder $query) => $query
                ->whereNull('meta_robots')
                ->orWhere('meta_robots', 'not like', '%noindex%'))
            ->latest('featured')
            ->latest('published_at')
            ->limit(25)
            ->get()
            ->flatMap(function (Post $post): array {
                $lines = [
                    "- {$this->plainText($post->title)}: {$this->postUrl($post)}",
                ];

                $summary = $post->ai_summary ?: $post->excerpt;
                if ($summary) {
                    $lines[] = '  Summary: '.Str::limit($this->plainText($summary), 300, '');
                }

                return $lines;
            })
            ->all();
    }

    private function indexablePosts(Builder $query): Builder
    {
        return $query
            ->published()
            ->where(fn (Builder $query) => $query
                ->whereNull('meta_robots')
                ->orWhere('meta_robots', 'not like', '%noindex%'));
    }

    private function url(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    private function postUrl(Post $post): string
    {
        $canonicalUrl = trim((string) $post->canonical_url);

        return $canonicalUrl !== ''
            ? $canonicalUrl
            : $this->url("/posts/{$post->slug}");
    }

    private function plainText(?string $value): string
    {
        return Str::squish(strip_tags((string) $value));
    }
}
