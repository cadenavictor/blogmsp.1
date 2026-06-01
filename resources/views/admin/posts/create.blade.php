@extends('layouts.admin', ['title' => 'Novo post'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.posts.index') }}">Posts</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.posts.store') }}">
            @csrf
            @include('admin.posts.form')
        </form>
    </section>
@endsection
