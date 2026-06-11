@extends('layouts.public', [
    'seoMeta' => app(\App\Services\SeoMetaBuilder::class)->forPost($post),
])

@section('content')
    <section class="article-band">
        <div class="wrap article-layout">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Início</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('categories.show', $post->category->slug) }}">{{ $post->category->name }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $post->title }}</span>
            </nav>

            <article class="article">
                <header class="article-header">
                    <p class="section-kicker">{{ $post->category->name }}</p>
                    <h1>{{ $post->title }}</h1>
                    @if ($post->excerpt)
                        <p class="lead">{{ $post->excerpt }}</p>
                    @endif
                    <p class="meta">
                        @if ($post->published_at)
                            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time>
                            <span aria-hidden="true">/</span>
                        @endif
                        <a href="{{ route('authors.show', $post->author) }}">{{ $post->author->name }}</a>
                        <span aria-hidden="true">/</span>
                        <span>{{ $post->reading_time }} min de leitura</span>
                    </p>
                </header>

                @if ($post->cover_url)
                    <figure class="article-cover">
                        <img src="{{ $post->cover_url }}" alt="{{ $post->title }}">
                    </figure>
                @endif

                @if (! empty($post->key_takeaways))
                    <section class="takeaways" aria-label="Principais pontos">
                        <h2>Principais pontos</h2>
                        <ul>
                            @foreach ($post->key_takeaways as $takeaway)
                                <li>{{ $takeaway }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <div class="content">
                    {!! $post->content !!}
                </div>

                @if (! empty($post->faq_items))
                    <section class="faq" aria-label="Perguntas frequentes">
                        <h2>Perguntas frequentes</h2>
                        @foreach ($post->faq_items as $faq)
                            @if (! empty($faq['question']) && ! empty($faq['answer']))
                                <details>
                                    <summary>{{ $faq['question'] }}</summary>
                                    <p>{{ $faq['answer'] }}</p>
                                </details>
                            @endif
                        @endforeach
                    </section>
                @endif

                @if ($post->tags->isNotEmpty())
                    <footer class="tags" aria-label="Tags">
                        @foreach ($post->tags as $tag)
                            <a class="tag" href="{{ route('tags.show', $tag->slug) }}">#{{ $tag->name }}</a>
                        @endforeach
                    </footer>
                @endif
            </article>
        </div>
    </section>
@endsection
