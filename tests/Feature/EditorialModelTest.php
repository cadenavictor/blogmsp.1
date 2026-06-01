<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EditorialModelTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_only_published_posts_are_publicly_queryable(): void
    {
        Carbon::setTestNow('2026-05-31 12:00:00');

        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Noticias',
            'slug' => 'noticias',
        ]);

        $published = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Published Post',
            'slug' => 'published-post',
            'content' => 'Public content.',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => 'Draft content.',
            'status' => 'draft',
            'published_at' => now()->subMinute(),
        ]);

        Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Future Post',
            'slug' => 'future-post',
            'content' => 'Future content.',
            'status' => 'published',
            'published_at' => now()->addMinute(),
        ]);

        $publicSlugs = Post::published()->pluck('slug')->all();

        $this->assertSame([$published->slug], $publicSlugs);
    }

    public function test_post_belongs_to_category_author_and_tags(): void
    {
        $author = User::factory()->create();
        $category = Category::create([
            'name' => 'Analises',
            'slug' => 'analises',
            'description' => 'Editorial analysis.',
            'seo_title' => 'Analises SEO',
            'seo_description' => 'Analises para SEO.',
        ]);
        $tags = collect([
            Tag::create([
                'name' => 'Laravel',
                'slug' => 'laravel',
                'description' => 'Laravel posts.',
            ]),
            Tag::create([
                'name' => 'PHP',
                'slug' => 'php',
            ]),
        ]);

        $post = Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Editorial Model',
            'slug' => 'editorial-model',
            'excerpt' => 'A short summary.',
            'content' => 'Long-form content.',
            'cover_image_path' => 'covers/editorial.jpg',
            'status' => 'published',
            'featured' => true,
            'published_at' => now(),
            'scheduled_at' => now()->addDay(),
            'seo_title' => 'Editorial Model SEO',
            'seo_description' => 'SEO description.',
            'canonical_url' => 'https://example.com/editorial-model',
            'meta_robots' => 'index,follow',
            'og_title' => 'Open Graph title',
            'og_description' => 'Open Graph description.',
            'ai_summary' => 'AI generated summary.',
            'key_takeaways' => ['Use relationships', 'Cast JSON fields'],
            'entities' => ['framework' => 'Laravel'],
            'faq_items' => [
                ['question' => 'Does it relate tags?', 'answer' => 'Yes.'],
            ],
        ]);
        $post->tags()->attach($tags->pluck('id'));
        $post->refresh();

        $this->assertTrue($post->author->is($author));
        $this->assertTrue($post->category->is($category));
        $this->assertEqualsCanonicalizing(
            $tags->pluck('id')->all(),
            $post->tags->pluck('id')->all(),
        );
        $this->assertTrue($category->posts->contains($post));
        $this->assertTrue($tags->first()->posts->contains($post));
        $this->assertTrue($post->featured);
        $this->assertInstanceOf(Carbon::class, $post->published_at);
        $this->assertInstanceOf(Carbon::class, $post->scheduled_at);
        $this->assertSame(['Use relationships', 'Cast JSON fields'], $post->key_takeaways);
        $this->assertSame(['framework' => 'Laravel'], $post->entities);
        $this->assertSame([
            ['question' => 'Does it relate tags?', 'answer' => 'Yes.'],
        ], $post->faq_items);
    }
}
