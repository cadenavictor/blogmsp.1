<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {--name= : Nome do administrador}
        {--email= : E-mail de login}
        {--password= : Senha (sera pedida de forma oculta se omitida)}';

    protected $description = 'Cria (ou atualiza) um usuario administrador do painel.';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nome', 'Administrador');
        $email = $this->option('email') ?: $this->ask('E-mail');
        $password = $this->option('password') ?: $this->secret('Senha (minimo 8 caracteres)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password], // o cast "hashed" do User cuida do hash
        );

        $user->forceFill(['is_admin' => true])->save();

        $this->info("Administrador pronto: {$user->email} (id {$user->id}).");
        $this->line('Acesse /login no painel com esse e-mail e senha.');

        return self::SUCCESS;
    }
}
