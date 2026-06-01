<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');

        if (app()->isProduction() && ($adminPassword === null || $adminPassword === 'password')) {
            throw new RuntimeException('ADMIN_PASSWORD must be set to a secure value in production.');
        }

        $admin = User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@blogmsp.local'),
        ], [
            'name' => env('ADMIN_NAME', 'Administrador'),
            'password' => Hash::make($adminPassword ?? 'password'),
        ]);

        $admin->forceFill(['is_admin' => true])->save();
    }
}
