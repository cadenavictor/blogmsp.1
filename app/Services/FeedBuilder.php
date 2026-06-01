<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FeedBuilder
{
    public function rss(): string
    {
        $posts = Post::query()
            ->published()
            ->where(fn (Builder $query) => $query
                ->whereNull('meta_robots')
                ->orWhere('meta_robots', 'not like', '%noindex%'))
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(20)
            ->get();

        $items = $posts
            ->map(fn (Post $post): string => $this->item($post))
            ->implode("\n");

        $updatedAt = $posts->max('updated_at') ?? now();
        $siteName = (string) config('app.name', 'Blog MSP');
        $siteUrl = $this->url('/');

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<rss version=\"2.0\">\n"
            ."    <channel>\n"
            ."        <title>{$this->escape($siteName)}</title>\n"
            ."        <link>{$this->escape($siteUrl)}</link>\n"
            ."        <description>{$this->escape($siteName)} - artigos, guias e novidades publicados.</description>\n"
            ."        <lastBuildDate>{$this->date($updatedAt)}</lastBuildDate>\n"
            .$items."\n"
            ."    </channel>\n"
            ."</rss>\n";
    }

    private function item(Post $post): string
    {
        $url = $this->postUrl($post);
        $description = $post->excerpt ?: Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags($post->content))), 240, '');

        return "        <item>\n"
            ."            <title>{$this->escape($post->title)}</title>\n"
            ."            <link>{$this->escape($url)}</link>\n"
            ."            <guid isPermaLink=\"true\">{$this->escape($url)}</guid>\n"
            ."            <description>{$this->escape($description)}</description>\n"
            ."            <pubDate>{$this->date($post->published_at)}</pubDate>\n"
            ."        </item>";
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

    private function date(mixed $value): string
    {
        return ($value instanceof Carbon ? $value : Carbon::parse($value))->toRfc7231String();
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
