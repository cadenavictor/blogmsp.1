@extends('layouts.admin', ['title' => 'Nova categoria'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.categories.index') }}">Voltar</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories.form')
        </form>
    </section>
@endsection
