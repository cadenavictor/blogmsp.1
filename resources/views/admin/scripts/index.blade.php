@extends('layouts.admin', ['title' => 'Scripts'])

@section('page-actions')
    <a class="button" href="{{ route('admin.scripts.create') }}">Novo script</a>
@endsection

@section('content')
    <section class="panel">
        @if ($snippets->count())
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Provider</th>
                            <th>Posicao</th>
                            <th>Ativo</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($snippets as $snippet)
                            <tr>
                                <td>{{ $snippet->name }}</td>
                                <td>{{ $snippet->provider }}</td>
                                <td>{{ $snippet->position }}</td>
                                <td>{{ $snippet->is_active ? 'Sim' : 'Nao' }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.scripts.edit', $snippet) }}">Editar</a>
                                        <form method="POST" action="{{ route('admin.scripts.destroy', $snippet) }}">
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
                {{ $snippets->links() }}
            </div>
        @else
            <p class="empty">Nenhum script encontrado.</p>
        @endif
    </section>
@endsection
