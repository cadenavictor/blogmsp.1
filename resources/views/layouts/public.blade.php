<!DOCTYPE html>
<html lang="{{ ($siteSettings ?? null)?->language ?: 'pt-BR' }}">
<head>
    @php
        $scriptSnippetsByPosition = $scriptSnippetsByPosition ?? collect();
    @endphp
    @foreach ($scriptSnippetsByPosition->get('head_start', collect()) as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
    @php
        $seoMeta = $seoMeta ?? app(\App\Services\SeoMetaBuilder::class)->forPage($title ?? 'Blog MSP');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoMeta['title'] }}</title>
    @if (! empty($seoMeta['description']))
        <meta name="description" content="{{ $seoMeta['description'] }}">
    @endif
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
    <meta name="robots" content="{{ $seoMeta['robots'] }}">
    @foreach (($seoMeta['openGraph'] ?? []) as $property => $content)
        @if (! empty($content))
            <meta property="{{ $property }}" content="{{ $content }}">
        @endif
    @endforeach
    @foreach (($seoMeta['twitter'] ?? []) as $name => $content)
        @if (! empty($content))
            <meta name="{{ $name }}" content="{{ $content }}">
        @endif
    @endforeach
    @foreach (($seoMeta['jsonLd'] ?? []) as $structuredData)
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endforeach
    @php $faviconUrl = ($siteSettings ?? null)?->imageUrl(($siteSettings ?? null)?->favicon_path); @endphp
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <style>
        :root {
            color-scheme: light;
            --bg: #f8fafc;
            --panel: #ffffff;
            --text: #172033;
            --muted: #657083;
            --line: #d8dee8;
            --accent: #0f766e;
            --accent-dark: #115e59;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
        }

        a {
            color: var(--accent-dark);
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        .site-header {
            background: var(--panel);
            border-bottom: 1px solid var(--line);
        }

        .wrap {
            width: min(1080px, calc(100% - 32px));
            margin: 0 auto;
        }

        .header-inner {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: space-between;
            padding: 18px 0;
        }

        .brand {
            color: var(--text);
            font-size: 1.15rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .brand-logo {
            display: block;
            max-height: 40px;
            width: auto;
        }

        .nav {
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        input[type="search"] {
            width: min(240px, 48vw);
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 9px 10px;
            font: inherit;
        }

        button {
            border: 0;
            border-radius: 6px;
            background: var(--accent);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            padding: 9px 12px;
        }

        main {
            padding: 34px 0 48px;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .page-heading h1 {
            margin: 0 0 8px;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.1;
        }

        .muted {
            color: var(--muted);
        }

        .post-list {
            display: grid;
            gap: 18px;
        }

        .post-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 22px;
        }

        .post-card h2 {
            margin: 0 0 10px;
            font-size: 1.45rem;
            line-height: 1.2;
        }

        .meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 0 0 12px;
            color: var(--muted);
            font-size: .95rem;
        }

        .breadcrumb {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 0 0 22px;
            color: var(--muted);
            font-size: .95rem;
        }

        .article {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: clamp(24px, 5vw, 42px);
        }

        .article h1 {
            margin: 0 0 14px;
            font-size: clamp(2rem, 5vw, 3.6rem);
            line-height: 1.05;
        }

        .content {
            margin-top: 28px;
            font-size: 1.08rem;
        }

        .content h2 {
            font-size: 1.6rem;
            line-height: 1.2;
            margin: 1.6em 0 .5em;
        }

        .content h3 {
            font-size: 1.3rem;
            margin: 1.3em 0 .4em;
        }

        .content p {
            margin: 0 0 1em;
        }

        .content ul,
        .content ol {
            margin: 0 0 1em;
            padding-left: 1.4em;
        }

        .content li {
            margin: .35em 0;
        }

        .content img {
            border-radius: 8px;
            height: auto;
            max-width: 100%;
        }

        .content blockquote {
            border-left: 4px solid var(--accent);
            color: var(--muted);
            margin: 1.2em 0;
            padding: 4px 0 4px 16px;
        }

        .content pre {
            background: #0f172a;
            border-radius: 8px;
            color: #e2e8f0;
            overflow-x: auto;
            padding: 14px 16px;
        }

        .takeaways {
            background: #f1f5f9;
            border: 1px solid var(--line);
            border-radius: 8px;
            margin: 28px 0 0;
            padding: 18px 22px;
        }

        .takeaways h2,
        .faq h2 {
            font-size: 1.3rem;
            margin: 0 0 10px;
        }

        .faq {
            margin-top: 32px;
        }

        .faq details {
            border-bottom: 1px solid var(--line);
            padding: 12px 0;
        }

        .faq summary {
            cursor: pointer;
            font-weight: 700;
        }

        .faq details p {
            margin: 10px 0 0;
            color: var(--muted);
        }

        .ai-summary {
            background: #eef6f4;
            border-left: 4px solid var(--accent);
            border-radius: 6px;
            margin: 24px 0 0;
            padding: 16px 18px;
        }

        .tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 26px;
        }

        .tag {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px 10px;
            text-decoration: none;
        }

        .pagination {
            margin-top: 24px;
        }

        .pagination nav > div:first-child {
            display: none;
        }

        .pagination nav > div:last-child {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .empty {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 22px;
        }

        @media (max-width: 720px) {
            .header-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .search {
                width: 100%;
            }

            input[type="search"] {
                flex: 1;
                width: auto;
            }
        }
    </style>
    @foreach ($scriptSnippetsByPosition->get('head_end', collect()) as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
</head>
<body>
    @foreach ($scriptSnippetsByPosition->get('body_start', collect()) as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
    <header class="site-header">
        <div class="wrap header-inner">
            <a class="brand" href="{{ route('home') }}">
                @php $brandLogo = ($siteSettings ?? null)?->imageUrl(($siteSettings ?? null)?->logo_path); @endphp
                @if ($brandLogo)
                    <img class="brand-logo" src="{{ $brandLogo }}" alt="{{ ($siteSettings ?? null)?->siteName() ?? config('app.name', 'Blog MSP') }}">
                @else
                    {{ ($siteSettings ?? null)?->siteName() ?? config('app.name', 'Blog MSP') }}
                @endif
            </a>
            <nav class="nav" aria-label="Principal">
                <a href="{{ route('home') }}">Inicio</a>
                <form class="search" action="{{ route('search') }}" method="get">
                    <label for="site-search" class="muted">Buscar</label>
                    <input id="site-search" type="search" name="q" value="{{ $searchQuery ?? request('q') }}" placeholder="Pesquisar posts">
                    <button type="submit">Buscar</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="wrap">
        @yield('content')
    </main>
    @foreach ($scriptSnippetsByPosition->get('body_end', collect()) as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
</body>
</html>
