@extends('layouts.admin', ['title' => 'Posts'])

@section('page-actions')
    <a class="button" href="{{ route('admin.posts.create') }}">Novo post</a>
@endsection

@section('content')
    <div class="stack">
        <section class="panel">
            <form class="filters" method="GET" action="{{ route('admin.posts.index') }}">
                <label class="field">
                    <span>Busca</span>
                    <input name="q" value="{{ $filters['q'] }}">
                </label>

                <label class="field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Todos</option>
                        @foreach (['draft' => 'Rascunho', 'published' => 'Publicado', 'scheduled' => 'Agendado'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field">
                    <span>Categoria</span>
                    <select name="category_id">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field">
                    <span>Tag</span>
                    <select name="tag_id">
                        <option value="">Todas</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected((string) $filters['tag_id'] === (string) $tag->id)>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit">Filtrar</button>
            </form>
        </section>

        <section class="panel">
            @if ($posts->count())
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titulo</th>
                                <th>Status</th>
                                <th>Categoria</th>
                                <th>Autor</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                                <tr>
                                    <td>{{ $post->title }}</td>
                                    <td><span class="pill status-{{ $post->status }}">{{ ucfirst($post->status) }}</span></td>
                                    <td>{{ $post->category->name }}</td>
                                    <td>{{ $post->author->name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.posts.edit', $post) }}">Editar</a>
                                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="Remover o post {{ $post->title }}?">
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
                    {{ $posts->links() }}
                </div>
            @else
                <p class="empty">Nenhum post encontrado.</p>
            @endif
        </section>
    </div>
@endsection
