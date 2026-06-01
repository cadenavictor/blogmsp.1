<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Painel - Blog MSP</title>
    </head>
    <body>
        <main>
            <h1>Painel</h1>

            <nav aria-label="Administracao">
                <a href="{{ route('admin.posts.index') }}">Posts</a>
                <a href="{{ route('admin.scripts.index') }}">Scripts</a>
                <a href="{{ route('admin.indexnow.index') }}">IndexNow</a>
            </nav>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Sair</button>
            </form>
        </main>
    </body>
</html>
