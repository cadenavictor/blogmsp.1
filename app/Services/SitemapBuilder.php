<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SitemapBuilder
{
    public function index(): string
    {
        return $this->sitemapIndex([
            $this->url('/sitemap-posts.xml'),
            $this->url('/sitemap-categories.xml'),
            $this->url('/sitemap-tags.xml'),
            $this->url('/sitemap-pages.xml'),
        ]);
    }

    public function posts(): string
    {
        $entries = Post::query()
            ->published()
            ->where(fn (Builder $query) => $query
                ->whereNull('meta_robots')
                ->orWhere('meta_robots', 'not like', '%noindex%'))
            ->latest('published_at')
            ->get()
            ->map(fn (Post $post): array => [
                'loc' => $this->postUrl($post),
                'lastmod' => $this->date($post->updated_at ?? $post->published_at),
            ])
            ->all();

        return $this->urlSet($entries);
    }

    public function categories(): string
    {
        $entries = Category::query()
            ->whereHas('posts', fn (Builder $query) => $this->indexablePosts($query))
            ->withMax(['posts as last_post_updated_at' => fn (Builder $query) => $this->indexablePosts($query)], 'updated_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'loc' => $this->url("/categorias/{$category->slug}"),
                'lastmod' => $this->date($category->last_post_updated_at ?? $category->updated_at),
            ])
            ->all();

        return $this->urlSet($entries);
    }

    public function tags(): string
    {
        $entries = Tag::query()
            ->whereHas('posts', fn (Builder $query) => $this->indexablePosts($query))
            ->withMax(['posts as last_post_updated_at' => fn (Builder $query) => $this->indexablePosts($query)], 'updated_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Tag $tag): array => [
                'loc' => $this->url("/tags/{$tag->slug}"),
                'lastmod' => $this->date($tag->last_post_updated_at ?? $tag->updated_at),
            ])
            ->all();

        return $this->urlSet($entries);
    }

    public function pages(): string
    {
        return $this->urlSet([
            [
                'loc' => $this->url('/'),
                'lastmod' => $this->date(
                    Post::query()
                        ->published()
                        ->where(fn (Builder $query) => $query
                            ->whereNull('meta_robots')
                            ->orWhere('meta_robots', 'not like', '%noindex%'))
                        ->max('updated_at') ?? now()
                ),
            ],
        ]);
    }

    private function indexablePosts(Builder $query): Builder
    {
        return $query
            ->published()
            ->where(fn (Builder $query) => $query
                ->whereNull('meta_robots')
                ->orWhere('meta_robots', 'not like', '%noindex%'));
    }

    /**
     * @param  array<int, string>  $locations
     */
    private function sitemapIndex(array $locations): string
    {
        $items = collect($locations)
            ->map(fn (string $location): string => sprintf(
                "    <sitemap>\n        <loc>%s</loc>\n    </sitemap>",
                $this->escape($location),
            ))
            ->implode("\n");

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            .$items."\n"
            ."</sitemapindex>\n";
    }

    /**
     * @param  array<int, array{loc: string, lastmod: string|null}>  $entries
     */
    private function urlSet(array $entries): string
    {
        $items = collect($entries)
            ->map(function (array $entry): string {
                $lastmod = $entry['lastmod'] ? "\n        <lastmod>{$this->escape($entry['lastmod'])}</lastmod>" : '';

                return "    <url>\n        <loc>{$this->escape($entry['loc'])}</loc>{$lastmod}\n    </url>";
            })
            ->implode("\n");

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            .$items."\n"
            ."</urlset>\n";
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

    private function date(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $value instanceof Carbon
            ? $value->toDateString()
            : Carbon::parse($value)->toDateString();
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
