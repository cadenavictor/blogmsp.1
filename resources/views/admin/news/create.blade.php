@extends('layouts.admin', ['title' => 'Novo monitoramento'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.news.index') }}">Voltar</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.news.store') }}">
            @csrf
            @include('admin.news.form')
        </form>
    </section>
@endsection
