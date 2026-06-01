# Integração Codex — Blog MSP

Ferramentas para o Codex (ou qualquer agente) ler notícias e **publicar artigos otimizados** via API.

## Setup

```bash
# Produção (site publicado):
export BLOG_API_URL="https://SEU-SITE.com"
export BLOG_API_KEY="<CODEX_API_KEY do .env de produção>"
node codex/blog.mjs ping
```

## Uso rápido

```bash
# 1) Puxar os TEMAS definidos no blog + notícias de cada um (uma chamada)
node codex/blog.mjs research --limit 6
#    ou um tema específico:
node codex/blog.mjs research --monitor tecnologia

# 2) (o Codex escreve o artigo) ... 3) publicar
node codex/blog.mjs publish artigo.json

# Busca avulsa por palavras-chave (sem usar os temas salvos)
node codex/blog.mjs news inteligência artificial --limit 5
```

## Arquivos

- `blog.mjs` — cliente CLI sem dependências (Node >= 18).
- `examples/post.example.json` — exemplo de payload de publicação.
- `../AGENTS.md` — fluxo completo e boas práticas de GEO que o Codex deve seguir.
- `../docs/api/openapi.yaml` — especificação OpenAPI (também em `GET /api/openapi.yaml`).
