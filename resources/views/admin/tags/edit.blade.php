@extends('layouts.admin', ['title' => 'Editar tag'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.tags.index') }}">Voltar</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.tags.update', $tag) }}">
            @csrf
            @method('PUT')
            @include('admin.tags.form')
        </form>
    </section>
@endsection
