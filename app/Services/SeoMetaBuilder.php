<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Str;

class SeoMetaBuilder
{
    /**
     * @return array{
     *     title: string,
     *     description: string|null,
     *     canonical: string,
     *     robots: string,
     *     openGraph: array<string, string|null>,
     *     twitter: array<string, string|null>,
     *     jsonLd: array<int, array<string, mixed>>
     * }
     */
    public function forPage(?string $title = null, ?string $description = null, ?string $canonical = null): array
    {
        $title = $title ?: config('app.name', 'Blog MSP');
        $canonical = $canonical ?: url()->current();

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'openGraph' => [
                'og:type' => 'website',
                'og:title' => $title,
                'og:description' => $description,
                'og:url' => $canonical,
                'og:site_name' => config('app.name', 'Blog MSP'),
            ],
            'twitter' => [
                'twitter:card' => 'summary',
                'twitter:title' => $title,
                'twitter:description' => $description,
            ],
            'jsonLd' => [],
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     description: string|null,
     *     canonical: string,
     *     robots: string,
     *     openGraph: array<string, string|null>,
     *     twitter: array<string, string|null>,
     *     jsonLd: array<int, array<string, mixed>>
     * }
     */
    public function forPost(Post $post): array
    {
        $title = $post->seo_title ?: $post->title;
        $description = $post->seo_description ?: $post->excerpt ?: $this->descriptionFromContent($post->content);
        $canonical = $post->canonical_url ?: route('posts.show', $post->slug);
        $image = $this->imageUrl($post->cover_image_path);

        $meta = [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $post->meta_robots ?: 'index,follow',
            'openGraph' => [
                'og:type' => 'article',
                'og:title' => $post->og_title ?: $title,
                'og:description' => $post->og_description ?: $description,
                'og:url' => $canonical,
                'og:site_name' => config('app.name', 'Blog MSP'),
                'og:image' => $image,
            ],
            'twitter' => [
                'twitter:card' => $image ? 'summary_large_image' : 'summary',
                'twitter:title' => $post->og_title ?: $title,
                'twitter:description' => $post->og_description ?: $description,
                'twitter:image' => $image,
            ],
            'jsonLd' => [
                $this->blogPosting($post, $title, $description, $canonical, $image),
                $this->breadcrumbList($post, $canonical),
            ],
        ];

        return $meta;
    }

    private function descriptionFromContent(string $content): string
    {
        $content = trim((string) preg_replace('/\s+/', ' ', strip_tags($content)));

        return Str::limit($content, 160, '');
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, '//')) {
            return 'https:'.$path;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    /**
     * @return array<string, mixed>
     */
    private function blogPosting(
        Post $post,
        string $title,
        ?string $description,
        string $canonical,
        ?string $image,
    ): array {
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $title,
            'description' => $description,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author?->name,
            ],
            'mainEntityOfPage' => $canonical,
        ];

        if ($image) {
            $structuredData['image'] = $image;
        }

        if ($post->category) {
            $structuredData['articleSection'] = $post->category->name;
            $structuredData['category'] = $post->category->name;
        }

        $keywords = $this->keywords($post->entities);
        if ($keywords !== []) {
            $structuredData['keywords'] = $keywords;
        }

        return $this->withoutEmptyValues($structuredData);
    }

    /**
     * @return array<string, mixed>
     */
    private function breadcrumbList(Post $post, string $canonical): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Inicio',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $post->title,
                    'item' => $canonical,
                ],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function keywords(mixed $entities): array
    {
        if (! is_array($entities)) {
            return [];
        }

        $values = array_is_list($entities) ? $entities : array_values($entities);

        return collect($values)
            ->map(function (mixed $entity): ?string {
                if (is_string($entity) || is_numeric($entity)) {
                    return (string) $entity;
                }

                if (is_array($entity) && isset($entity['name']) && is_scalar($entity['name'])) {
                    return (string) $entity['name'];
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutEmptyValues(array $data): array
    {
        return collect($data)
            ->reject(fn (mixed $value): bool => $value === null || $value === '')
            ->all();
    }
}
