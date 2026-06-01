@extends('layouts.admin', ['title' => 'Resultados: '.$monitor->name])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.news.edit', $monitor) }}">Editar</a>
    <a class="button secondary" href="{{ route('admin.news.index') }}">Voltar</a>
@endsection

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>{{ $monitor->name }}</h2>
                    <p class="muted">
                        Palavras-chave: <code>{{ $monitor->keywords }}</code> &middot;
                        {{ $monitor->language }}/{{ $monitor->country }} &middot;
                        {{ count($items) }} resultado(s) &middot; cache de {{ (int) config('services.google_news.cache_ttl', 900) / 60 }} min
                    </p>
                </div>
                <a class="button secondary" href="{{ route('admin.news.show', $monitor) }}">Atualizar</a>
            </div>

            @if (count($items))
                <div class="news-grid">
                    @foreach ($items as $item)
                        <article class="news-card">
                            <header>
                                <h3><a href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer">{{ $item['title'] }}</a></h3>
                                <p class="news-meta">
                                    @if ($item['source'])<span>{{ $item['source'] }}</span>@endif
                                    @if ($item['published_at'])<time datetime="{{ $item['published_at'] }}">{{ \Carbon\Carbon::parse($item['published_at'])->diffForHumans() }}</time>@endif
                                </p>
                            </header>
                            @if ($item['snippet'])
                                <p class="news-snippet">{{ $item['snippet'] }}</p>
                            @endif
                            <footer class="news-actions">
                                <a class="button secondary" href="{{ route('admin.posts.create', ['title' => $item['title'], 'source_url' => $item['link']]) }}">Criar post</a>
                                <a class="button ghost" href="{{ $item['link'] }}" target="_blank" rel="noopener noreferrer">Abrir</a>
                            </footer>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty">Nenhum resultado retornado agora. O Google News pode nao ter itens para estas palavras-chave, ou houve falha de rede. Tente novamente em instantes.</p>
            @endif
        </section>
    </div>
@endsection
