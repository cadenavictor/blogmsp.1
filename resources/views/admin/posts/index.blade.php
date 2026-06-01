<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Posts - Blog MSP</title>
    </head>
    <body>
        <main>
            <header>
                <p><a href="{{ route('admin.dashboard') }}">Painel</a></p>
                <h1>Posts</h1>
                <p><a href="{{ route('admin.posts.create') }}">Novo post</a></p>
            </header>

            @if (session('status'))
                <p role="status">{{ session('status') }}</p>
            @endif

            <form method="GET" action="{{ route('admin.posts.index') }}">
                <label>
                    Busca
                    <input name="q" value="{{ $filters['q'] }}">
                </label>

                <label>
                    Status
                    <select name="status">
                        <option value="">Todos</option>
                        @foreach (['draft' => 'Rascunho', 'published' => 'Publicado', 'scheduled' => 'Agendado'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Categoria
                    <select name="category_id">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Tag
                    <select name="tag_id">
                        <option value="">Todas</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected((string) $filters['tag_id'] === (string) $tag->id)>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit">Filtrar</button>
            </form>

            @if ($posts->count())
                <table>
                    <thead>
                        <tr>
                            <th>Titulo</th>
                            <th>Status</th>
                            <th>Categoria</th>
                            <th>Autor</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr>
                                <td>{{ $post->title }}</td>
                                <td>{{ $post->status }}</td>
                                <td>{{ $post->category->name }}</td>
                                <td>{{ $post->author->name }}</td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $post) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $posts->links() }}
            @else
                <p>Nenhum post encontrado.</p>
            @endif
        </main>
    </body>
</html>
