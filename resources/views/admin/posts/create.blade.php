<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Novo post - Blog MSP</title>
    </head>
    <body>
        <main>
            <p><a href="{{ route('admin.posts.index') }}">Posts</a></p>
            <h1>Novo post</h1>

            <form method="POST" action="{{ route('admin.posts.store') }}">
                @csrf
                @include('admin.posts.form')
            </form>
        </main>
    </body>
</html>
