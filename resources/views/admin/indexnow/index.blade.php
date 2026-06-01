<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>IndexNow - Blog MSP</title>
    </head>
    <body>
        <main>
            <header>
                <p><a href="{{ route('admin.dashboard') }}">Painel</a></p>
                <h1>IndexNow</h1>
            </header>

            @if (session('status'))
                <p role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.indexnow.store') }}">
                @csrf
                <label for="url">URL</label>
                <input id="url" name="url" type="url" value="{{ old('url') }}" required>
                @error('url')
                    <p>{{ $message }}</p>
                @enderror
                <button type="submit">Enviar</button>
            </form>

            @if ($submissions->count())
                <table>
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Resposta</th>
                            <th>Enviada em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <tr>
                                <td>{{ $submission->url }}</td>
                                <td>{{ $submission->status_code ?? 'Pendente' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit((string) $submission->response_body, 120) }}</td>
                                <td>{{ $submission->submitted_at?->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $submissions->links() }}
            @else
                <p>Nenhum envio encontrado.</p>
            @endif
        </main>
    </body>
</html>
