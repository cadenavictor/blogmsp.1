# AGENTS.md — Guia do Codex para o Blog MSP

Este repositório é um blog em **Laravel 13** com uma **API protegida por chave** feita para você (Codex)
ler notícias e **publicar artigos completos e otimizados para SEO/GEO** de forma automatizada.

Você **escreve o conteúdo** (HTML semântico); o backend apenas **armazena e otimiza** (gera slug único,
deriva SEO/description/excerpt, reading time, JSON-LD `BlogPosting`/`FAQPage`/`Organization`).

## Arquitetura

O Codex roda **no PC do usuário** e fala por HTTPS com a API do **site publicado**. O próprio
blog faz o proxy do Google News (server-side) a partir dos **temas definidos no painel**
(monitoramentos). Ou seja: o Codex chama a API do blog — não o Google diretamente.

```
Codex (PC) ──HTTPS + X-Api-Key──▶  https://SEU-SITE  ──▶ Google News RSS (server-side)
     │                                     ▲
     └──────── POST /api/posts ────────────┘  (publica o artigo)
```

## Configuração (uma vez)

A API exige uma chave estática (`CODEX_API_KEY` no `.env` do site). Exporte para o cliente:

```bash
# PRODUÇÃO (site publicado):
export BLOG_API_URL="https://SEU-SITE.com"
export BLOG_API_KEY="<o valor de CODEX_API_KEY do .env de produção>"

# (local, para testes): export BLOG_API_URL="http://127.0.0.1:8000"
```

Teste a conexão:

```bash
node codex/blog.mjs ping
```

## Cliente CLI (`codex/blog.mjs`, sem dependências, Node >= 18)

| Comando | O que faz |
|---|---|
| `node codex/blog.mjs ping` | Testa a chave e lista endpoints |
| `node codex/blog.mjs research [--monitor slug] [--limit N]` | **Temas do blog + notícias** de cada um (uma chamada) |
| `node codex/blog.mjs news <palavras...> [--limit N]` | Lê o Google News por palavras-chave avulsas |
| `node codex/blog.mjs monitors` | Lista só os temas (monitoramentos) ativos |
| `node codex/blog.mjs categories` / `tags` | Taxonomia válida (use nomes existentes quando possível) |
| `node codex/blog.mjs posts [--q termo]` / `get <slug>` | Lista / lê posts publicados |
| `node codex/blog.mjs publish artigo.json` | **Publica** um post (corpo via arquivo ou STDIN) |

## Fluxo de publicação automatizada (a partir dos temas do blog)

1. **Puxe os temas + notícias** definidos no blog (uma chamada):
   `node codex/blog.mjs research --limit 6`
   → retorna cada monitoramento (`monitor.name`, `keywords`) com seus `items` (manchete, fonte, link).
   Para um tema específico: `node codex/blog.mjs research --monitor <slug>`.
2. **Escolha** um tema e **pesquise** as manchetes/fontes retornadas. **Não copie** texto; sintetize com suas palavras e cite as fontes com link `<a href>` no corpo.
3. **Escreva** o artigo seguindo as boas práticas de GEO abaixo.
4. **Monte o JSON** do post (veja o schema) e salve em `artigo.json` (ou gere por STDIN).
5. **Publique**: `node codex/blog.mjs publish artigo.json`
6. **Confira**: o comando retorna `data.url`. Opcionalmente `node codex/blog.mjs get <slug>`.

## Schema do post (`POST /api/posts`)

Apenas `title` e `content` são obrigatórios. O resto é auto-derivado se omitido — mas preencher melhora o GEO.

```json
{
  "title": "Como a IA está mudando o jornalismo",
  "content": "<p>Introdução...</p><h2>Seção</h2><p>...</p>",
  "excerpt": "Resumo de 1-2 frases para listagens e meta description.",
  "category": "Tecnologia",                 // id, slug ou nome (criado se não existir)
  "tags": ["IA", "jornalismo"],              // nomes (criados se não existirem)
  "status": "published",                     // published | draft | scheduled (default: published)
  "cover_image_url": "https://.../capa.jpg", // opcional
  "ai_summary": "Resumo objetivo para agentes de IA (2-3 frases).",
  "key_takeaways": ["Ponto 1", "Ponto 2", "Ponto 3"],
  "entities": ["OpenAI", "Google", "Perplexity"],
  "faq_items": [
    { "question": "O que é GEO?", "answer": "Resposta objetiva e completa." }
  ],
  "seo_title": "Opcional — senão usa o title",
  "seo_description": "Opcional — senão deriva do excerpt/conteúdo",
  "published_at": "2026-06-01T12:00:00-03:00" // opcional
}
```

## Boas práticas de GEO (Generative Engine Optimization)

O objetivo é ser **citado e recomendado por IAs** (ChatGPT, Gemini, Perplexity). Para isso:

- **Estrutura semântica**: use `<h2>`/`<h3>`, parágrafos curtos, `<ul>`/`<ol>` e `<blockquote>`. Comece com um parágrafo que **responde diretamente** à pergunta principal.
- **`key_takeaways`**: 3–6 conclusões objetivas e auto-contidas (cada uma faz sentido sozinha). É o que as IAs mais citam.
- **`faq_items`**: 2–5 perguntas reais com respostas completas. Vira `FAQPage` (schema) e seção visível.
- **`entities`**: nomes próprios relevantes (marcas, produtos, pessoas, lugares) — viram `keywords` no JSON-LD.
- **`ai_summary`**: um resumo limpo e factual para agentes.
- **Atribuição**: cite fontes com link `<a href>` no corpo. Não invente fatos nem números; baseie-se nas notícias retornadas.
- **Original**: nunca copie trechos das fontes; reescreva e agregue valor.
- **Título e excerpt** claros, com a entidade/tema principal logo no começo.

## Convenções do repositório (para tarefas de código)

- Stack: Laravel 13 + Blade + Vite/Tailwind. Painel admin dark em `/admin` (auth + middleware `admin`).
- Testes: `php artisan test` (roda em SQLite in-memory). Estilo: `vendor/bin/pint --dirty`.
- Rotas da API: `routes/api.php` (prefixo `/api`, middleware `api.key`). Lógica de criação: `app/Services/PostComposer.php`.
- Sanitização de HTML do conteúdo: `app/Services/HtmlSanitizer.php` (remove script/style/handlers).
- Documentação da API: `docs/api/openapi.yaml` e a página `/admin/integracoes`.
