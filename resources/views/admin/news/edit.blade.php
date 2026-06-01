@extends('layouts.admin', ['title' => 'Editar monitoramento'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.news.show', $monitor) }}">Ver resultados</a>
    <a class="button secondary" href="{{ route('admin.news.index') }}">Voltar</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.news.update', $monitor) }}">
            @csrf
            @method('PUT')
            @include('admin.news.form')
        </form>
    </section>
@endsection
