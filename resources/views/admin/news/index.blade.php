@extends('layouts.admin', ['title' => 'Google News'])

@section('page-actions')
    <a class="button" href="{{ route('admin.news.create') }}">Novo monitoramento</a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Monitoramentos</h2>
                <p class="muted">Buscas salvas no Google News. Visiveis aqui e consumiveis pela API (<code>/api/news?monitor=slug</code>).</p>
            </div>
        </div>

        @if ($monitors->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Palavras-chave</th>
                            <th>Idioma/Pais</th>
                            <th>Ativo</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monitors as $monitor)
                            <tr>
                                <td><a href="{{ route('admin.news.show', $monitor) }}">{{ $monitor->name }}</a></td>
                                <td><code>{{ $monitor->keywords }}</code></td>
                                <td>{{ $monitor->language }} / {{ $monitor->country }}</td>
                                <td>
                                    <span class="pill {{ $monitor->is_active ? 'pill-ok' : 'pill-off' }}">
                                        {{ $monitor->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.news.show', $monitor) }}">Resultados</a>
                                        <a href="{{ route('admin.news.edit', $monitor) }}">Editar</a>
                                        <form method="POST" action="{{ route('admin.news.destroy', $monitor) }}" data-confirm="Remover {{ $monitor->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="danger" type="submit">Remover</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $monitors->links() }}
            </div>
        @else
            <p class="empty">Nenhum monitoramento ainda. <a href="{{ route('admin.news.create') }}">Crie o primeiro</a> para acompanhar temas no Google News.</p>
        @endif
    </section>
@endsection
