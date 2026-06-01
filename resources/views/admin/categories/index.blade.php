@extends('layouts.admin', ['title' => 'Categorias'])

@section('page-actions')
    <a class="button" href="{{ route('admin.categories.create') }}">Nova categoria</a>
@endsection

@section('content')
    <section class="panel">
        @if ($categories->count())
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
                        @foreach ($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td><span class="pill">{{ $category->posts_count }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}">Editar</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Remover a categoria {{ $category->name }}?">
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
                {{ $categories->links() }}
            </div>
        @else
            <p class="empty">Nenhuma categoria cadastrada. <a href="{{ route('admin.categories.create') }}">Crie a primeira</a>.</p>
        @endif
    </section>
@endsection
