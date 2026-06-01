@extends('layouts.admin', ['title' => 'Nova tag'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.tags.index') }}">Voltar</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.tags.store') }}">
            @csrf
            @include('admin.tags.form')
        </form>
    </section>
@endsection
