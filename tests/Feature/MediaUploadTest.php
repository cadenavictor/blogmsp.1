<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_upload(): void
    {
        $this->post('/admin/media')->assertRedirect('/login');
    }

    public function test_admin_can_upload_image(): void
    {
        Storage::fake('uploads');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->image('cover.jpg', 1200, 630),
        ]);

        $response->assertCreated()->assertJsonStructure(['path', 'url']);

        $this->assertStringStartsWith('uploads/', $response->json('path'));
        $this->assertStringContainsString('/uploads/', $response->json('url'));
        $this->assertStringNotContainsString('/storage/', $response->json('url'));

        Storage::disk('uploads')->assertExists(str($response->json('path'))->after('uploads/')->toString());
    }

    public function test_non_image_is_rejected(): void
    {
        Storage::fake('uploads');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson('/admin/media', ['file' => 'not-an-image'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }
}
