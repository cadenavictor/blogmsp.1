<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/tags')->assertForbidden();
    }

    public function test_admin_can_create_tag_with_auto_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/tags', [
            'name' => 'Machine Learning',
            'slug' => '',
        ])->assertRedirect('/admin/tags');

        $this->assertDatabaseHas('tags', [
            'name' => 'Machine Learning',
            'slug' => 'machine-learning',
        ]);
    }

    public function test_admin_can_destroy_tag(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $tag = Tag::create(['name' => 'Temp', 'slug' => 'temp']);

        $this->actingAs($admin)
            ->delete("/admin/tags/{$tag->id}")
            ->assertRedirect('/admin/tags');

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
