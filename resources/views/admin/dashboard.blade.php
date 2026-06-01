@extends('layouts.admin', ['title' => 'Painel'])

@section('page-actions')
    <a class="button" href="{{ route('admin.posts.create') }}">Novo post</a>
@endsection

@section('content')
    <div class="stack">
        <section class="metric-grid" aria-label="Resumo editorial">
            <div class="metric">
                <span>Total</span>
                <strong>{{ $totalPosts }}</strong>
            </div>
            @foreach ($statusCounts as $statusCount)
                <div class="metric">
                    <span>{{ $statusCount['label'] }}</span>
                    <strong>{{ $statusCount['count'] }}</strong>
                </div>
            @endforeach
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Posts recentes</h2>
                    <p class="muted">Ultimos itens editados no fluxo editorial.</p>
                </div>
                <a class="button secondary" href="{{ route('admin.posts.index') }}">Ver posts</a>
            </div>

            @if ($recentPosts->count())
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titulo</th>
                                <th>Status</th>
                                <th>Categoria</th>
                                <th>Autor</th>
                                <th>Publicado em</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPosts as $post)
                                <tr>
                                    <td>{{ $post->title }}</td>
                                    <td>{{ $post->status }}</td>
                                    <td>{{ $post->category->name }}</td>
                                    <td>{{ $post->author->name }}</td>
                                    <td>{{ $post->published_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.posts.edit', $post) }}">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty">Nenhum post cadastrado.</p>
            @endif
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Arquivos de maquina</h2>
                    <p class="muted">Atalhos para conferir saidas consumidas por buscadores e agentes.</p>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Arquivo</th>
                            <th>Caminho</th>
                            <th>Uso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($machineFiles as $machineFile)
                            <tr>
                                <td><a href="{{ $machineFile['url'] }}">{{ $machineFile['label'] }}</a></td>
                                <td><code>{{ $machineFile['path'] }}</code></td>
                                <td>{{ $machineFile['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Operacoes</h2>
                    <p class="muted">Atalhos para manutencao editorial e tecnica.</p>
                </div>
            </div>
            <div class="admin-actions">
                <a class="button secondary" href="{{ route('admin.posts.index') }}">Posts</a>
                <a class="button secondary" href="{{ route('admin.scripts.index') }}">Scripts</a>
                <a class="button secondary" href="{{ route('admin.indexnow.index') }}">IndexNow</a>
            </div>
        </section>
    </div>
@endsection
