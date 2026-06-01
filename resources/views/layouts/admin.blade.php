<!doctype html>
<html lang="pt-BR">
    <head>
        @php $siteSettings = \App\Models\SiteSetting::current(); @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Admin' }} - {{ $siteSettings->siteName() }}</title>
        @php $faviconUrl = $siteSettings->imageUrl($siteSettings->favicon_path); @endphp
        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    </head>
    <body class="admin-shell">
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    @php $adminLogo = $siteSettings->imageUrl($siteSettings->logo_path); @endphp
                    @if ($adminLogo)
                        <img class="admin-brand-logo" src="{{ $adminLogo }}" alt="{{ $siteSettings->siteName() }}">
                    @else
                        <span class="dot"></span> {{ $siteSettings->siteName() }}
                    @endif
                </a>

                <nav class="admin-nav" aria-label="Administracao">
                    <p class="nav-group">Conteudo</p>
                    <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif><span class="ic">▦</span> Painel</a>
                    <a href="{{ route('admin.posts.index') }}" @if (request()->routeIs('admin.posts.*')) aria-current="page" @endif><span class="ic">✎</span> Posts</a>
                    <a href="{{ route('admin.categories.index') }}" @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif><span class="ic">▤</span> Categorias</a>
                    <a href="{{ route('admin.tags.index') }}" @if (request()->routeIs('admin.tags.*')) aria-current="page" @endif><span class="ic">#</span> Tags</a>

                    <p class="nav-group">Descoberta</p>
                    <a href="{{ route('admin.news.index') }}" @if (request()->routeIs('admin.news.*')) aria-current="page" @endif><span class="ic">📰</span> Google News</a>
                    <a href="{{ route('admin.indexnow.index') }}" @if (request()->routeIs('admin.indexnow.*')) aria-current="page" @endif><span class="ic">⚡</span> IndexNow</a>
                    <a href="{{ route('admin.scripts.index') }}" @if (request()->routeIs('admin.scripts.*')) aria-current="page" @endif><span class="ic">{ }</span> Scripts</a>

                    <p class="nav-group">Sistema</p>
                    <a href="{{ route('admin.settings.seo.edit') }}" @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif><span class="ic">⚙</span> SEO &amp; Site</a>
                    <a href="{{ route('admin.integracoes') }}" @if (request()->routeIs('admin.integracoes')) aria-current="page" @endif><span class="ic">⇄</span> API &amp; Codex</a>
                    <a href="{{ route('feed') }}" target="_blank" rel="noopener"><span class="ic">»</span> Feed / Sitemap</a>
                </nav>
            </aside>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div>
                        <p class="admin-eyebrow">Administracao</p>
                        <h1>{{ $title ?? 'Admin' }}</h1>
                    </div>
                    <div class="admin-actions">
                        @yield('page-actions')
                        @auth
                            <span class="admin-user">Ola, <strong>{{ auth()->user()->name }}</strong></span>
                        @endauth
                        <form class="logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="secondary" type="submit">Sair</button>
                        </form>
                    </div>
                </header>

                <main class="admin-content">
                    @if (session('status'))
                        <p class="alert success" role="status">{{ session('status') }}</p>
                    @endif

                    @if (session('error'))
                        <p class="alert error" role="alert">{{ session('error') }}</p>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
