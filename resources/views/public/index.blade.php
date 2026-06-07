@extends('layouts.public', ['title' => $title ?? 'Melhores de São Paulo'])

@section('content')
    @php
        $postItems = $posts->getCollection();
        $featuredPost = $postItems->first();
        $isHome = request()->routeIs('home');
        $listingTitle = $isHome ? 'Últimas análises' : 'Posts publicados';
        $listingDescription = $isHome
            ? 'Conteúdo editorial para comparar empresas e serviços sem ruído de anúncios.'
            : 'Arquivo editorial com os posts publicados para este filtro.';
    @endphp

    @if ($isHome)
        <section class="home-hero" aria-labelledby="page-title">
            <div class="hero-media" aria-hidden="true">
                <img src="{{ asset('images/melhores-sp-hero.png') }}" alt="">
            </div>
            <div class="wrap hero-content">
                <div class="hero-copy">
                    <p class="section-kicker">Curadoria independente</p>
                    <h1 id="page-title">{{ $title }}</h1>
                    @if (! empty($subtitle))
                        <p class="lead">{{ $subtitle }}</p>
                    @endif
                    <div class="hero-actions">
                        <a class="button-link" href="#ultimas-analises">Ver análises</a>
                        <a class="text-link" href="#criterios">Conhecer critérios</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="criterios" class="criteria-band">
            <div class="wrap criteria-grid">
                <div>
                    <p class="section-kicker">Como avaliamos</p>
                    <h2>Informação útil antes da escolha</h2>
                </div>
                <ul class="criteria-list" aria-label="Critérios editoriais">
                    <li><strong>Reputação</strong><span>Histórico, atendimento e sinais públicos de confiança.</span></li>
                    <li><strong>Experiência</strong><span>Clareza do serviço, jornada do cliente e consistência.</span></li>
                    <li><strong>Contexto local</strong><span>Bairros atendidos, faixa de preço e adequação à cidade.</span></li>
                </ul>
            </div>
        </section>
    @else
        <section class="page-heading-band" aria-labelledby="page-title">
            <div class="wrap">
                <p class="section-kicker">Arquivo editorial</p>
                <h1 id="page-title">{{ $title }}</h1>
                @if (! empty($subtitle))
                    <p class="lead">{{ $subtitle }}</p>
                @endif
            </div>
        </section>
    @endif

    <section id="ultimas-analises" class="content-band">
        <div class="wrap">
            <div class="section-header">
                <div>
                    <p class="section-kicker">Reviews, guias e notícias</p>
                    <h2>{{ $listingTitle }}</h2>
                </div>
                <p>{{ $listingDescription }}</p>
            </div>

            @if ($posts->count())
                @if ($featuredPost)
                    <article class="featured-post">
                        <div class="featured-label">Destaque editorial</div>
                        <p class="meta">
                            @if ($featuredPost->published_at)
                                <time datetime="{{ $featuredPost->published_at->toDateString() }}">{{ $featuredPost->published_at->format('d/m/Y') }}</time>
                                <span aria-hidden="true">/</span>
                            @endif
                            <a href="{{ route('categories.show', $featuredPost->category->slug) }}">{{ $featuredPost->category->name }}</a>
                            <span aria-hidden="true">/</span>
                            <a href="{{ route('authors.show', $featuredPost->author) }}">{{ $featuredPost->author->name }}</a>
                        </p>
                        <h3><a href="{{ route('posts.show', $featuredPost->slug) }}">{{ $featuredPost->title }}</a></h3>
                        @if ($featuredPost->excerpt)
                            <p>{{ $featuredPost->excerpt }}</p>
                        @endif
                    </article>
                @endif

                <div class="post-list" aria-label="Posts">
                    @foreach ($postItems as $post)
                        <article class="post-card">
                            <p class="meta">
                                @if ($post->published_at)
                                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time>
                                    <span aria-hidden="true">/</span>
                                @endif
                                <a href="{{ route('categories.show', $post->category->slug) }}">{{ $post->category->name }}</a>
                                <span aria-hidden="true">/</span>
                                <a href="{{ route('authors.show', $post->author) }}">{{ $post->author->name }}</a>
                            </p>
                            <h3>
                                <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            @if ($post->excerpt)
                                <p>{{ $post->excerpt }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $posts->links() }}
                </div>
            @else
                <p class="empty">Nenhum post publicado encontrado.</p>
            @endif
        </div>
    </section>
@endsection
