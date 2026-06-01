@extends('layouts.admin', ['title' => 'API & Codex'])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2>Status da integracao</h2>
                    <p class="muted">A API e protegida por uma chave estatica enviada no header <code>X-Api-Key</code> (ou <code>Authorization: Bearer</code>).</p>
                </div>
            </div>
            <p>
                Chave <code>CODEX_API_KEY</code>:
                @if ($keyConfigured)
                    <span class="pill pill-ok">configurada</span>
                @else
                    <span class="pill pill-off">nao configurada</span>
                @endif
            </p>
            @unless ($keyConfigured)
                <p class="alert error">Defina <code>CODEX_API_KEY</code> no arquivo <code>.env</code> com um valor longo e aleatorio, depois rode <code>php artisan config:clear</code>. Enquanto estiver vazia, a API responde <code>503</code>.</p>
            @endunless
            <p class="muted">Base URL: <code>{{ $baseUrl }}/api</code></p>
        </section>

        <section class="panel">
            <div class="panel-header"><div><h2>Ler Google News</h2></div></div>
            <div class="endpoint"><span class="verb verb-get">GET</span> <code>/api/news?q=palavras-chave</code></div>
            <div class="endpoint"><span class="verb verb-get">GET</span> <code>/api/news?monitor=slug-do-monitoramento</code></div>
            <p class="muted">Parametros opcionais: <code>language</code> (hl), <code>country</code> (gl), <code>limit</code> (1-50).</p>
            <div class="codeblock">
                <button type="button" class="button secondary copy" data-copy="#ex-news">Copiar</button>
                <pre id="ex-news">curl -s "{{ $baseUrl }}/api/news?q=intelig%C3%AAncia%20artificial&limit=10" \
  -H "X-Api-Key: SUA_CHAVE"</pre>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header"><div><h2>Criar post completo e otimizado</h2></div></div>
            <div class="endpoint"><span class="verb verb-post">POST</span> <code>/api/posts</code></div>
            <p class="muted">Envie <code>title</code> e <code>content</code> (HTML). Campos ausentes de SEO/GEO sao derivados automaticamente (slug unico, seo_title, seo_description, excerpt, og, reading time). <code>category</code> e <code>tags</code> aceitam id, slug ou nome (criados se nao existirem).</p>
            <div class="codeblock">
                <button type="button" class="button secondary copy" data-copy="#ex-post">Copiar</button>
                <pre id="ex-post">curl -s -X POST "{{ $baseUrl }}/api/posts" \
  -H "X-Api-Key: SUA_CHAVE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Como a IA esta mudando o jornalismo",
    "content": "&lt;p&gt;Texto completo do post em HTML...&lt;/p&gt;&lt;h2&gt;Secao&lt;/h2&gt;&lt;p&gt;...&lt;/p&gt;",
    "category": "Tecnologia",
    "tags": ["IA", "jornalismo"],
    "status": "published",
    "key_takeaways": ["Ponto 1", "Ponto 2"],
    "entities": ["OpenAI", "Google"],
    "faq_items": [{"question": "O que e?", "answer": "Resposta."}]
  }'</pre>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header"><div><h2>Outros endpoints</h2></div></div>
            <div class="endpoint"><span class="verb verb-get">GET</span> <code>/api/ping</code> — testa a chave e lista endpoints</div>
            <div class="endpoint"><span class="verb verb-get">GET</span> <code>/api/news/monitors</code> — monitoramentos ativos</div>
            <div class="endpoint"><span class="verb verb-get">GET</span> <code>/api/categories</code> &middot; <code>/api/tags</code> — taxonomia valida</div>
            <div class="endpoint"><span class="verb verb-get">GET</span> <code>/api/posts</code> &middot; <code>/api/posts/{slug}</code> — listar/ler posts</div>
        </section>
    </div>
@endsection
