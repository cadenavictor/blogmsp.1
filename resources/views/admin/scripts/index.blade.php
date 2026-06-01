<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Scripts - Blog MSP</title>
    </head>
    <body>
        <main>
            <header>
                <p><a href="{{ route('admin.dashboard') }}">Painel</a></p>
                <h1>Scripts</h1>
                <p><a href="{{ route('admin.scripts.create') }}">Novo script</a></p>
            </header>

            @if (session('status'))
                <p role="status">{{ session('status') }}</p>
            @endif

            @if ($snippets->count())
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Provider</th>
                            <th>Posicao</th>
                            <th>Ativo</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($snippets as $snippet)
                            <tr>
                                <td>{{ $snippet->name }}</td>
                                <td>{{ $snippet->provider }}</td>
                                <td>{{ $snippet->position }}</td>
                                <td>{{ $snippet->is_active ? 'Sim' : 'Nao' }}</td>
                                <td>
                                    <a href="{{ route('admin.scripts.edit', $snippet) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.scripts.destroy', $snippet) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $snippets->links() }}
            @else
                <p>Nenhum script encontrado.</p>
            @endif
        </main>
    </body>
</html>
