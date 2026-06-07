<!doctype html>
<html lang="{{ ($siteSettings ?? null)?->language ?: 'pt-BR' }}">
<head>
    @php
        $scriptSnippetsByPosition = $scriptSnippetsByPosition ?? collect();
        $siteName = ($siteSettings ?? null)?->siteName() ?? 'Melhores de São Paulo';
        $immediateScriptSnippets = fn (string $position) => $scriptSnippetsByPosition
            ->get($position, collect())
            ->reject(fn ($scriptSnippet): bool => (bool) $scriptSnippet->requires_consent);
        $consentScriptSnippets = fn (string $position) => $scriptSnippetsByPosition
            ->get($position, collect())
            ->filter(fn ($scriptSnippet): bool => (bool) $scriptSnippet->requires_consent);
    @endphp
    @foreach ($immediateScriptSnippets('head_start') as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
    @php
        $seoMeta = $seoMeta ?? app(\App\Services\SeoMetaBuilder::class)->forPage($title ?? $siteName);
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @foreach ($immediateScriptSnippets('head_end') as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
</head>
<body>
    @foreach ($immediateScriptSnippets('body_start') as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach

    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

    <header class="site-header">
        <div class="site-topline">
            <div class="wrap topline-inner">
                <span>Curadoria independente para escolher melhor em São Paulo</span>
            </div>
        </div>

        <div class="wrap header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ $siteName }}">
                @php $brandLogo = ($siteSettings ?? null)?->imageUrl(($siteSettings ?? null)?->logo_path); @endphp
                @if ($brandLogo)
                    <img class="brand-logo" src="{{ $brandLogo }}" alt="{{ $siteName }}">
                @else
                    <span class="brand-mark">MSP</span>
                    <span class="brand-copy">
                        <strong>{{ $siteName }}</strong>
                        <small>Reviews, guias e notícias locais</small>
                    </span>
                @endif
            </a>

            <nav class="nav" aria-label="Principal">
                <a href="{{ route('home') }}">Início</a>
                <a href="{{ route('home') }}#ultimas-analises">Reviews</a>
                <a href="{{ route('home') }}#criterios">Critérios</a>
                <a href="{{ route('feed') }}">Feed</a>
            </nav>

            <form class="search" action="{{ route('search') }}" method="get" role="search">
                <label for="site-search" class="sr-only">Buscar no blog</label>
                <input id="site-search" type="search" name="q" value="{{ $searchQuery ?? request('q') }}" placeholder="Buscar empresas, serviços, bairros">
                <button type="submit">Buscar</button>
            </form>
        </div>
    </header>

    <main id="conteudo" class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="wrap footer-grid">
            <div>
                <p class="footer-brand">{{ $siteName }}</p>
                <p>Jornalismo de serviço, reviews e guias para decisões melhores na capital paulista.</p>
            </div>
            <section class="footer-privacy" aria-label="LGPD, privacidade e cookies">
                <p class="footer-section-title">LGPD e cookies</p>
                <strong>Aviso de cookies</strong>
                <p>Usamos cookies essenciais para o site funcionar e, com sua permissão, scripts opcionais de análise. Saiba mais na <a href="{{ route('privacy') }}">Política de Privacidade</a>.</p>
                <div class="cookie-actions" data-cookie-banner>
                    <button type="button" class="button-secondary" data-cookie-reject>Recusar</button>
                    <button type="button" data-cookie-accept>Aceitar</button>
                </div>
            </section>
            <nav class="footer-links" aria-label="Links institucionais">
                <a href="{{ route('privacy') }}">Política de Privacidade</a>
                <a href="{{ route('sitemap.index') }}">Sitemap</a>
                <a href="{{ route('llms') }}">llms.txt</a>
                <a href="{{ route('robots') }}">robots.txt</a>
            </nav>
        </div>
    </footer>

    @foreach (\App\Models\ScriptSnippet::positions() as $scriptPosition)
        @foreach ($consentScriptSnippets($scriptPosition) as $scriptSnippet)
            <template data-cookie-snippet data-cookie-category="{{ $scriptSnippet->cookie_category ?: \App\Models\ScriptSnippet::COOKIE_CATEGORY_ANALYTICS }}" data-cookie-position="{{ $scriptPosition }}">{{ $scriptSnippet->content }}</template>
        @endforeach
    @endforeach

    @foreach ($immediateScriptSnippets('body_end') as $scriptSnippet)
        {!! $scriptSnippet->content !!}
    @endforeach
</body>
</html>
