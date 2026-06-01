<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_categories_index(): void
    {
        $this->get('/admin/categories')->assertRedirect('/login');
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Inteligencia Artificial',
            'slug' => '',
            'description' => 'Tudo sobre IA.',
        ])->assertRedirect('/admin/categories');

        $this->assertDatabaseHas('categories', [
            'name' => 'Inteligencia Artificial',
            'slug' => 'inteligencia-artificial',
        ]);
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Category::create(['name' => 'Tech', 'slug' => 'tech']);

        $this->actingAs($admin)->from('/admin/categories/create')->post('/admin/categories', [
            'name' => 'Outra Tech',
            'slug' => 'tech',
        ])->assertRedirect('/admin/categories/create')->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Velho', 'slug' => 'velho']);

        $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Novo nome',
            'slug' => 'novo-nome',
        ])->assertRedirect("/admin/categories/{$category->id}/edit");

        $this->assertSame('Novo nome', $category->refresh()->name);
    }

    public function test_destroy_is_blocked_when_category_has_posts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Com posts', 'slug' => 'com-posts']);
        Post::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Post',
            'slug' => 'post',
            'content' => 'Conteudo.',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_destroy_empty_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Vazia', 'slug' => 'vazia']);

        $this->actingAs($admin)
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
