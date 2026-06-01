<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Editar post - Blog MSP</title>
    </head>
    <body>
        <main>
            <p><a href="{{ route('admin.posts.index') }}">Posts</a></p>
            <h1>Editar post</h1>

            @if (session('status'))
                <p role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.posts.update', $post) }}">
                @csrf
                @method('PUT')
                @include('admin.posts.form')
            </form>
        </main>
    </body>
</html>
