@extends('layouts.admin', ['title' => 'IndexNow'])

@section('content')
    <div class="stack">
        <section class="panel">
            <form class="filters" method="POST" action="{{ route('admin.indexnow.store') }}">
                @csrf
                <label class="field" for="url">
                    <span>URL</span>
                    <input id="url" name="url" type="url" value="{{ old('url') }}" required>
                </label>
                <button type="submit">Enviar</button>
            </form>

            @error('url')
                <p class="alert error" role="alert">{{ $message }}</p>
            @enderror
        </section>

        <section class="panel">
            @if ($submissions->count())
                <div class="admin-table-wrap">
                    <table class="admin-table">
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
                </div>

                <div class="pagination">
                    {{ $submissions->links() }}
                </div>
            @else
                <p class="empty">Nenhum envio encontrado.</p>
            @endif
        </section>
    </div>
@endsection
