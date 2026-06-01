@extends('layouts.public', [
    'seoMeta' => app(\App\Services\SeoMetaBuilder::class)->forPost($post),
])

@section('content')
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Inicio</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('categories.show', $post->category->slug) }}">{{ $post->category->name }}</a>
        <span aria-hidden="true">/</span>
        <span>{{ $post->title }}</span>
    </nav>

    <article class="article">
        <header>
            <p class="meta">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d/m/Y') }}</time>
                    <span aria-hidden="true">/</span>
                @endif
                <a href="{{ route('authors.show', $post->author) }}">{{ $post->author->name }}</a>
            </p>
            <h1>{{ $post->title }}</h1>
            @if ($post->excerpt)
                <p class="muted">{{ $post->excerpt }}</p>
            @endif
            @if ($post->ai_summary)
                <p class="ai-summary">{{ $post->ai_summary }}</p>
            @endif
        </header>

        <div class="content">
            {!! nl2br(e($post->content)) !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <footer class="tags" aria-label="Tags">
                @foreach ($post->tags as $tag)
                    <a class="tag" href="{{ route('tags.show', $tag->slug) }}">#{{ $tag->name }}</a>
                @endforeach
            </footer>
        @endif
    </article>
@endsection
