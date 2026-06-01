<?php

namespace Tests\Feature;

use App\Models\IndexNowSubmission;
use App\Models\User;
use App\Services\IndexNowClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class IndexNowTest extends TestCase
{
    use RefreshDatabase;

    public function test_indexnow_client_posts_url_list_and_records_result(): void
    {
        config(['app.url' => 'http://127.0.0.1:8000']);

        Http::fake([
            'api.indexnow.org/*' => Http::response('Accepted', 200),
        ]);

        app(IndexNowClient::class)->submit([
            'http://127.0.0.1:8000/posts/example',
        ]);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.indexnow.org/indexnow'
                && $request->method() === 'POST'
                && $request['host'] === '127.0.0.1:8000'
                && $request['urlList'] === ['http://127.0.0.1:8000/posts/example'];
        });

        $this->assertDatabaseHas('index_now_submissions', [
            'url' => 'http://127.0.0.1:8000/posts/example',
            'status_code' => 200,
        ]);
    }

    public function test_indexnow_key_file_route_serves_configured_key(): void
    {
        config(['services.indexnow.key' => 'configured-indexnow-key']);

        $this->get('/configured-indexnow-key.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('configured-indexnow-key');

        $this->get('/wrong-indexnow-key.txt')->assertNotFound();
    }

    public function test_indexnow_client_rejects_predictable_default_key_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        Http::fake();

        foreach (['', 'local-indexnow-key'] as $key) {
            config(['services.indexnow.key' => $key]);

            try {
                app(IndexNowClient::class)->submit([
                    'https://blog.example.test/posts/example',
                ]);

                $this->fail('Expected RuntimeException for unsafe production IndexNow key.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('INDEXNOW_KEY', $exception->getMessage());
            }
        }

        Http::assertNothingSent();
        $this->assertDatabaseCount('index_now_submissions', 0);
    }

    public function test_indexnow_key_file_route_rejects_default_key_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['services.indexnow.key' => 'local-indexnow-key']);

        $this->withoutExceptionHandling();
        $this->expectException(RuntimeException::class);

        $this->get('/local-indexnow-key.txt');
    }

    public function test_indexnow_key_file_route_is_limited_to_safe_key_shape(): void
    {
        $route = collect(app('router')->getRoutes())->firstWhere('action.as', 'indexnow.key');

        $this->assertSame('[A-Za-z0-9_-]{8,128}', $route?->wheres['indexNowKey'] ?? null);

        config(['services.indexnow.key' => 'configured-indexnow-key']);

        $this->get('/qualquer.txt')->assertNotFound();
        $this->get('/short.txt')->assertNotFound();
    }

    public function test_guest_is_redirected_from_admin_indexnow_index(): void
    {
        $this->get('/admin/indexnow')->assertRedirect('/login');
    }

    public function test_admin_can_submit_indexnow_url_from_admin_form(): void
    {
        config([
            'app.url' => 'https://blog.example.test',
            'services.indexnow.key' => 'admin-indexnow-key',
        ]);

        Http::fake([
            'api.indexnow.org/*' => Http::response('Submitted from admin', 200),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post('/admin/indexnow', [
                'url' => 'https://blog.example.test/posts/admin-submit',
            ])
            ->assertRedirect('/admin/indexnow')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('index_now_submissions', [
            'url' => 'https://blog.example.test/posts/admin-submit',
            'status_code' => 200,
        ]);
    }

    public function test_admin_submit_rejects_urls_outside_configured_app_host(): void
    {
        config([
            'app.url' => 'https://blog.example.test',
            'services.indexnow.key' => 'admin-indexnow-key',
        ]);

        Http::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from('/admin/indexnow')
            ->post('/admin/indexnow', [
                'url' => 'https://other.example.test/posts/admin-submit',
            ])
            ->assertRedirect('/admin/indexnow')
            ->assertSessionHasErrors('url');

        Http::assertNothingSent();
        $this->assertDatabaseCount('index_now_submissions', 0);
    }

    public function test_admin_submit_rejects_localhost_when_app_url_is_public_domain(): void
    {
        config([
            'app.url' => 'https://blog.example.test',
            'services.indexnow.key' => 'admin-indexnow-key',
        ]);

        Http::fake();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from('/admin/indexnow')
            ->post('/admin/indexnow', [
                'url' => 'http://127.0.0.1:8000/posts/admin-submit',
            ])
            ->assertRedirect('/admin/indexnow')
            ->assertSessionHasErrors('url');

        Http::assertNothingSent();
        $this->assertDatabaseCount('index_now_submissions', 0);
    }

    public function test_admin_submit_allows_same_host_and_port_as_development_app_url(): void
    {
        config([
            'app.url' => 'http://127.0.0.1:8000',
            'services.indexnow.key' => 'admin-indexnow-key',
        ]);

        Http::fake([
            'api.indexnow.org/*' => Http::response('Submitted from local admin', 200),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post('/admin/indexnow', [
                'url' => 'http://127.0.0.1:8000/posts/admin-submit',
            ])
            ->assertRedirect('/admin/indexnow')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('index_now_submissions', [
            'url' => 'http://127.0.0.1:8000/posts/admin-submit',
            'status_code' => 200,
        ]);
    }

    public function test_indexnow_client_records_each_url_when_transport_exception_occurs(): void
    {
        config([
            'app.url' => 'https://blog.example.test',
            'services.indexnow.key' => 'exception-indexnow-key',
        ]);

        Http::fake([
            'api.indexnow.org/*' => fn () => throw new ConnectionException(str_repeat('Network timeout ', 400)),
        ]);

        app(IndexNowClient::class)->submit([
            'https://blog.example.test/posts/one',
            'https://blog.example.test/posts/two',
        ]);

        foreach (['one', 'two'] as $slug) {
            $submission = IndexNowSubmission::query()
                ->where('url', "https://blog.example.test/posts/{$slug}")
                ->firstOrFail();

            $this->assertNull($submission->status_code);
            $this->assertStringContainsString(ConnectionException::class, $submission->response_body);
            $this->assertLessThanOrEqual(4000, strlen($submission->response_body));
        }
    }

    public function test_admin_submit_does_not_explode_when_indexnow_transport_fails(): void
    {
        config([
            'app.url' => 'https://blog.example.test',
            'services.indexnow.key' => 'exception-indexnow-key',
        ]);

        Http::fake([
            'api.indexnow.org/*' => fn () => throw new ConnectionException('Network timeout'),
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post('/admin/indexnow', [
                'url' => 'https://blog.example.test/posts/admin-submit',
            ])
            ->assertRedirect('/admin/indexnow')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('index_now_submissions', [
            'url' => 'https://blog.example.test/posts/admin-submit',
            'status_code' => null,
        ]);
    }

    public function test_indexnow_response_body_is_truncated_for_success_and_failure_results(): void
    {
        config([
            'app.url' => 'https://blog.example.test',
            'services.indexnow.key' => 'truncate-indexnow-key',
        ]);

        Http::fake([
            'api.indexnow.org/*' => Http::sequence()
                ->push(str_repeat('S', 4500), 200)
                ->push(str_repeat('F', 4500), 500),
        ]);

        app(IndexNowClient::class)->submit([
            'https://blog.example.test/posts/success',
        ]);

        app(IndexNowClient::class)->submit([
            'https://blog.example.test/posts/failure',
        ]);

        $bodies = IndexNowSubmission::query()
            ->orderBy('url')
            ->pluck('response_body', 'url');

        $this->assertSame(4000, strlen($bodies['https://blog.example.test/posts/failure']));
        $this->assertSame(4000, strlen($bodies['https://blog.example.test/posts/success']));
    }
}
