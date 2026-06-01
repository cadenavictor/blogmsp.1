@extends('layouts.admin', ['title' => 'Painel'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.news.index') }}">Google News</a>
    <a class="button" href="{{ route('admin.posts.create') }}">Novo post</a>
@endsection

@section('content')
    <div class="stack">
        <section class="metric-grid" aria-label="Resumo editorial">
            <div class="metric">
                <span>Posts</span>
                <strong>{{ $totalPosts }}</strong>
            </div>
            @foreach ($statusCounts as $statusCount)
                <div class="metric">
                    <span>{{ $statusCount['label'] }}</span>
                    <strong>{{ $statusCount['count'] }}</strong>
                </div>
            @endforeach
        </section>

        <section class="metric-grid" aria-label="Taxonomia e descoberta">
            <div class="metric">
                <span>Categorias</span>
                <strong>{{ $totalCategories }}</strong>
            </div>
            <div class="metric">
                <span>Tags</span>
                <strong>{{ $totalTags }}</strong>
            </div>
            <div class="metric">
                <span>Monitores News</span>
                <strong>{{ $activeMonitors }}</strong>
            </div>
            <div class="metric">
                <span>API Codex</span>
                <strong>{!! $apiKeyConfigured ? '<span class="status-published" style="font-size:1rem;padding:4px 10px">Ativa</span>' : '<span class="status-draft" style="font-size:1rem;padding:4px 10px">Off</span>' !!}</strong>
            </div>
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
                                    <td><span class="pill status-{{ $post->status }}">{{ ucfirst($post->status) }}</span></td>
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
                <p class="empty">Nenhum post cadastrado. <a href="{{ route('admin.posts.create') }}">Crie o primeiro</a>.</p>
            @endif
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Google News</h2>
                    <p class="muted">Monitoramento de temas para pauta editorial e automacao via Codex.</p>
                </div>
                <a class="button secondary" href="{{ route('admin.news.index') }}">Gerenciar</a>
            </div>
            @if ($featuredMonitor)
                <p>Monitoramento em destaque: <a href="{{ route('admin.news.show', $featuredMonitor) }}"><strong>{{ $featuredMonitor->name }}</strong></a> &middot; <code>{{ $featuredMonitor->keywords }}</code></p>
            @else
                <p class="empty">Nenhum monitoramento ativo. <a href="{{ route('admin.news.create') }}">Crie um monitoramento</a> de palavras-chave do Google News.</p>
            @endif
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Integracoes &amp; API</h2>
                    <p class="muted">Endpoints consumidos pelo Codex para ler noticias e publicar posts.</p>
                </div>
                <a class="button secondary" href="{{ route('admin.integracoes') }}">Ver documentacao</a>
            </div>
            <p>
                Status da chave <code>CODEX_API_KEY</code>:
                @if ($apiKeyConfigured)
                    <span class="pill pill-ok">configurada</span>
                @else
                    <span class="pill pill-off">nao configurada</span> — defina <code>CODEX_API_KEY</code> no <code>.env</code> para liberar a API.
                @endif
            </p>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Arquivos de maquina</h2>
                    <p class="muted">Saidas consumidas por buscadores e agentes.</p>
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
    </div>
@endsection
