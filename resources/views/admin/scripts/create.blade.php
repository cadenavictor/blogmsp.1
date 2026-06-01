@extends('layouts.admin', ['title' => 'Novo script'])

@section('page-actions')
    <a class="button secondary" href="{{ route('admin.scripts.index') }}">Scripts</a>
@endsection

@section('content')
    <section class="panel">
        <form class="admin-form" method="POST" action="{{ route('admin.scripts.store') }}">
            @csrf
            @include('admin.scripts.form')
        </form>
    </section>
@endsection
