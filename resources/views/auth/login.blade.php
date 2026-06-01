<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login - Blog MSP</title>
        @vite(['resources/css/admin.css'])
    </head>
    <body class="login-shell">
        <main class="login-card">
            <p class="admin-brand"><span class="dot"></span> Blog MSP</p>
            <p class="muted">Acesso ao painel administrativo</p>

            @if ($errors->any())
                <div class="alert error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <label>
                    Email
                    <input name="email" type="email" value="{{ old('email') }}" required autofocus>
                </label>

                <label>
                    Senha
                    <input name="password" type="password" required>
                </label>

                <label class="check-field">
                    <input name="remember" type="checkbox" value="1">
                    Lembrar acesso
                </label>

                <button type="submit">Entrar</button>
            </form>
        </main>
    </body>
</html>
