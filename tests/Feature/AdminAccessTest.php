<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_view_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Painel');
    }

    public function test_authenticated_non_admin_receives_forbidden_from_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_valid_login_redirects_to_admin_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'is_admin' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
    }

    public function test_invalid_login_returns_error_and_does_not_authenticate(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'is_admin' => true,
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_authentication_and_redirects_to_login(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_login_post_is_rate_limited_after_five_attempts(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => 'missing@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $response = $this->post('/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertSame(429, $response->getStatusCode());
    }

    public function test_database_seeder_uses_admin_environment_credentials(): void
    {
        $this->withAdminEnvironment(
            name: 'Env Admin',
            email: 'env-admin@example.com',
            password: 'env-secret-password',
            callback: function (): void {
                $this->runDatabaseSeeder();

                $admin = User::where('email', 'env-admin@example.com')->firstOrFail();

                $this->assertSame('Env Admin', $admin->name);
                $this->assertTrue($admin->is_admin);
                $this->assertTrue(Hash::check('env-secret-password', $admin->password));
            },
        );
    }

    public function test_database_seeder_rejects_public_admin_password_in_production(): void
    {
        $this->withApplicationEnvironment('production', function (): void {
            $this->withAdminEnvironment(
                name: null,
                email: null,
                password: 'password',
                callback: function (): void {
                    $this->expectException(RuntimeException::class);

                    $this->runDatabaseSeeder();
                },
            );
        });
    }

    public function test_database_seeder_requires_admin_password_in_production(): void
    {
        $this->withApplicationEnvironment('production', function (): void {
            $this->withAdminEnvironment(
                name: null,
                email: null,
                password: null,
                callback: function (): void {
                    $this->expectException(RuntimeException::class);

                    $this->runDatabaseSeeder();
                },
            );
        });
    }

    public function test_admin_flag_cannot_be_mass_assigned(): void
    {
        $user = new User;

        $user->fill([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->assertFalse((bool) $user->is_admin);
    }

    public function test_register_route_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    private function withApplicationEnvironment(string $environment, callable $callback): void
    {
        $previousEnvironment = $this->app->environment();

        $this->app->detectEnvironment(fn (): string => $environment);

        try {
            $callback();
        } finally {
            $this->app->detectEnvironment(fn (): string => $previousEnvironment);
        }
    }

    private function runDatabaseSeeder(): void
    {
        $this->app->make(DatabaseSeeder::class)->run();
    }

    private function withAdminEnvironment(
        ?string $name,
        ?string $email,
        ?string $password,
        callable $callback,
    ): void {
        $values = [
            'ADMIN_NAME' => $name,
            'ADMIN_EMAIL' => $email,
            'ADMIN_PASSWORD' => $password,
        ];
        $previous = [];

        foreach ($values as $key => $value) {
            $previous[$key] = getenv($key);
            $this->setEnvironmentValue($key, $value);
        }

        try {
            $callback();
        } finally {
            foreach ($previous as $key => $value) {
                $this->setEnvironmentValue($key, $value === false ? null : $value);
            }
        }
    }

    private function setEnvironmentValue(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
