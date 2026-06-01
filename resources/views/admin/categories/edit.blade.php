@extends('layouts.admin', ['title' => 'Editar categoria'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.categories.index') }}">Voltar</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('admin.categories.form')
        </form>
    </section>
@endsection
