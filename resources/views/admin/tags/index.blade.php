@extends('layouts.admin', ['title' => 'Tags'])

@section('page-actions')
    <a class="button" href="{{ route('admin.tags.create') }}">Nova tag</a>
@endsection

@section('content')
    <section class="panel">
        @if ($tags->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Posts</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tags as $tag)
                            <tr>
                                <td>{{ $tag->name }}</td>
                                <td><code>{{ $tag->slug }}</code></td>
                                <td><span class="pill">{{ $tag->posts_count }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.tags.edit', $tag) }}">Editar</a>
                                        <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" data-confirm="Remover a tag {{ $tag->name }}?">
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
                {{ $tags->links() }}
            </div>
        @else
            <p class="empty">Nenhuma tag cadastrada. <a href="{{ route('admin.tags.create') }}">Crie a primeira</a>.</p>
        @endif
    </section>
@endsection
