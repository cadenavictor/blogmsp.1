@extends('layouts.public', ['title' => $title ?? 'Blog MSP'])

@section('content')
    <section class="page-heading" aria-labelledby="page-title">
        <h1 id="page-title">{{ $title }}</h1>
        @if (! empty($subtitle))
            <p class="muted">{{ $subtitle }}</p>
        @endif
    </section>

    @if ($posts->count())
        <section class="post-list" aria-label="Posts">
            @foreach ($posts as $post)
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
                    <h2>
                        <a href="{{ route('posts.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    @if ($post->excerpt)
                        <p>{{ $post->excerpt }}</p>
                    @endif
                </article>
            @endforeach
        </section>

        <div class="pagination">
            {{ $posts->links() }}
        </div>
    @else
        <p class="empty">Nenhum post publicado encontrado.</p>
    @endif
@endsection
