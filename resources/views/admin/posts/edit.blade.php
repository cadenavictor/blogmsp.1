@extends('layouts.admin', ['title' => 'Editar post'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.posts.index') }}">Posts</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.posts.update', $post) }}">
            @csrf
            @method('PUT')
            @include('admin.posts.form')
        </form>
    </section>
@endsection
