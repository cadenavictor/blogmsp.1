<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SiteSetting;
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
        $settings = SiteSetting::current();
        $title = $title ?: $settings->siteName();
        $description = $description ?: $this->nullable($settings->default_meta_description);
        $canonical = $canonical ?: url()->current();
        $image = $settings->imageUrl($settings->default_og_image) ?? asset('images/melhores-sp-hero.png');

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'openGraph' => array_filter([
                'og:type' => 'website',
                'og:title' => $title,
                'og:description' => $description,
                'og:url' => $canonical,
                'og:site_name' => $settings->siteName(),
                'og:locale' => $settings->locale,
                'og:image' => $image,
            ], fn ($value) => $value !== null),
            'twitter' => array_filter([
                'twitter:card' => $image ? 'summary_large_image' : 'summary',
                'twitter:title' => $title,
                'twitter:description' => $description,
                'twitter:site' => $this->nullable($settings->twitter_handle),
                'twitter:image' => $image,
            ], fn ($value) => $value !== null),
            'jsonLd' => [],
        ];
    }

    /**
     * Home page: page meta + sitewide GEO signals (WebSite + Organization).
     *
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
    public function forHome(?string $title = null, ?string $description = null): array
    {
        $settings = SiteSetting::current();

        $title = $this->nullable($settings->home_title) ?? $title ?? $settings->siteName();
        $description = $this->nullable($settings->home_meta_description)
            ?? $description
            ?? $this->nullable($settings->default_meta_description)
            ?? $this->nullable($settings->tagline);

        $meta = $this->forPage($title, $description, route('home'));
        $meta['jsonLd'] = array_values(array_filter([
            $this->webSite($settings),
            $this->organization($settings),
        ]));

        return $meta;
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
        $settings = SiteSetting::current();

        $title = $post->seo_title ?: $post->title;
        $description = $post->seo_description ?: $post->excerpt ?: $this->descriptionFromContent($post->content);
        $canonical = $post->canonical_url ?: route('posts.show', $post->slug);
        $image = $this->imageUrl($post->cover_image_path) ?: $settings->imageUrl($settings->default_og_image);

        $openGraph = array_filter([
            'og:type' => 'article',
            'og:title' => $post->og_title ?: $title,
            'og:description' => $post->og_description ?: $description,
            'og:url' => $canonical,
            'og:site_name' => $settings->siteName(),
            'og:locale' => $settings->locale,
            'og:image' => $image,
            'article:published_time' => $post->published_at?->toAtomString(),
            'article:modified_time' => $post->updated_at?->toAtomString(),
        ], fn ($value) => $value !== null);

        $jsonLd = [
            $this->blogPosting($post, $settings, $title, $description, $canonical, $image),
            $this->breadcrumbList($post, $canonical),
        ];

        $faqPage = $this->faqPage($post);
        if ($faqPage !== null) {
            $jsonLd[] = $faqPage;
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $post->meta_robots ?: 'index,follow',
            'openGraph' => $openGraph,
            'twitter' => array_filter([
                'twitter:card' => $image ? 'summary_large_image' : 'summary',
                'twitter:title' => $post->og_title ?: $title,
                'twitter:description' => $post->og_description ?: $description,
                'twitter:site' => $this->nullable($settings->twitter_handle),
                'twitter:image' => $image,
            ], fn ($value) => $value !== null),
            'jsonLd' => $jsonLd,
        ];
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
        SiteSetting $settings,
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
            'inLanguage' => $settings->language,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => $this->withoutEmptyValues([
                '@type' => 'Person',
                'name' => $post->author?->name,
                'url' => $post->author ? route('authors.show', $post->author) : null,
            ]),
            'publisher' => $this->publisher($settings),
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
    private function publisher(SiteSetting $settings): array
    {
        $logo = $settings->imageUrl($settings->logo_path);

        return $this->withoutEmptyValues([
            '@type' => 'Organization',
            'name' => $settings->organizationName(),
            'logo' => $logo ? ['@type' => 'ImageObject', 'url' => $logo] : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function organization(SiteSetting $settings): array
    {
        $logo = $settings->imageUrl($settings->logo_path);
        $contactPoint = $this->withoutEmptyValues([
            '@type' => 'ContactPoint',
            'contactType' => 'customer support',
            'email' => $this->nullable($settings->contact_email),
            'telephone' => $this->nullable($settings->contact_phone),
        ]);

        return $this->withoutEmptyValues([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $settings->organizationName(),
            'url' => $settings->siteUrl(),
            'description' => $this->nullable($settings->organization_description),
            'logo' => $logo,
            'sameAs' => $settings->sameAs() ?: null,
            'contactPoint' => count($contactPoint) > 1 ? $contactPoint : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function webSite(SiteSetting $settings): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $settings->siteName(),
            'url' => $settings->siteUrl(),
            'inLanguage' => $settings->language,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $settings->siteUrl().'/buscar?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function faqPage(Post $post): ?array
    {
        $items = is_array($post->faq_items) ? $post->faq_items : [];

        $questions = collect($items)
            ->map(function ($item): ?array {
                $question = is_array($item) ? trim((string) ($item['question'] ?? '')) : '';
                $answer = is_array($item) ? trim((string) ($item['answer'] ?? '')) : '';

                if ($question === '' || $answer === '') {
                    return null;
                }

                return [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $answer,
                    ],
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($questions === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions,
        ];
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

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withoutEmptyValues(array $data): array
    {
        return collect($data)
            ->reject(fn (mixed $value): bool => $value === null || $value === '' || $value === [])
            ->all();
    }
}
