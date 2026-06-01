<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Novo script - Blog MSP</title>
    </head>
    <body>
        <main>
            <p><a href="{{ route('admin.scripts.index') }}">Scripts</a></p>
            <h1>Novo script</h1>

            <form method="POST" action="{{ route('admin.scripts.store') }}">
                @csrf
                @include('admin.scripts.form')
            </form>
        </main>
    </body>
</html>
