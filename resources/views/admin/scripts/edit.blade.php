<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar script - Blog MSP</title>
    </head>
    <body>
        <main>
            <p><a href="{{ route('admin.scripts.index') }}">Scripts</a></p>
            <h1>Editar script</h1>

            @if (session('status'))
                <p role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.scripts.update', $snippet) }}">
                @csrf
                @method('PUT')
                @include('admin.scripts.form')
            </form>
        </main>
    </body>
</html>
