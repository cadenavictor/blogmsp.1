# Integracao Codex - Blog MSP

Ferramentas para o Codex, ou qualquer agente autorizado, ler noticias e publicar artigos otimizados via API.

## Setup

```bash
# Producao (site publicado):
export BLOG_API_URL="https://SEU-SITE.com"
export BLOG_API_KEY="<CODEX_API_KEY do .env de producao>"
node codex/blog.mjs ping
```

Em ambiente local, o cliente tambem consegue ler `APP_URL` e `CODEX_API_KEY` do `.env` do Laravel. Em automacoes fora da pasta do projeto, prefira definir `BLOG_API_URL` e `BLOG_API_KEY`.

## Uso rapido

```bash
# 1) Puxar os temas definidos no blog + noticias de cada um
node codex/blog.mjs research --limit 6

# Tema especifico
node codex/blog.mjs research --monitor tecnologia

# Rotina diaria recomendada (publica direto como published)
node codex/daily-news-brief.mjs --limit 6 --recent 30

# O Codex escreve o artigo em artigo.json e publica
node codex/blog.mjs publish artigo.json

# Busca avulsa por palavras-chave
node codex/blog.mjs news "inteligencia artificial" --limit 5
```

## Rotina diaria

Use `prompts/daily-news-post.md` como instrucao operacional para a automacao diaria do Codex.

Fluxo:

1. `node codex/blog.mjs ping`
2. `node codex/daily-news-brief.mjs --limit 6 --recent 30`
3. escolher 1 noticia relevante para empresas, servicos e experiencias em Sao Paulo;
4. escrever artigo original, sem copiar fonte;
5. publicar com `status: "published"` via `node codex/blog.mjs publish artigo.json`;
6. registrar a URL retornada.

## Arquivos

- `blog.mjs` - cliente CLI sem dependencias (Node >= 18).
- `daily-news-brief.mjs` - monta o briefing diario para o Codex escolher uma noticia e publicar.
- `prompts/daily-news-post.md` - prompt operacional da rotina diaria com `status: "published"`.
- `examples/post.example.json` - exemplo de payload de publicacao.
- `../AGENTS.md` - fluxo completo e boas praticas de GEO que o Codex deve seguir.
- `../docs/api/openapi.yaml` - especificacao OpenAPI, tambem em `GET /api/openapi.yaml`.
