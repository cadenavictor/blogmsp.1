#!/usr/bin/env node
// Blog MSP — cliente de integracao para o Codex.
// Sem dependencias: usa fetch global (Node >= 18).
//
// Uso:
//   node codex/blog.mjs <comando> [opcoes]
//
// Configuracao (variaveis de ambiente):
//   BLOG_API_URL   base da API        (default: http://127.0.0.1:8000)
//   BLOG_API_KEY   chave da API       (obrigatoria; = CODEX_API_KEY do .env do blog)
//
// Comandos:
//   ping                              Testa a chave e lista os endpoints.
//   research                          Temas do blog (monitoramentos) + noticias de cada um.
//        --monitor <slug>             escopo para um tema; --limit <n> itens por tema
//   news <palavras...>                GET /api/news?q=...
//        --monitor <slug>             usa um monitoramento salvo no lugar de q
//        --limit <n> --language <hl> --country <gl>
//   monitors                          GET /api/news/monitors
//   categories | tags                 Taxonomia valida (id/slug/nome).
//   posts [--status s] [--q termo]    GET /api/posts
//   get <slug>                        GET /api/posts/{slug}
//   publish [arquivo.json]            POST /api/posts (corpo do arquivo ou STDIN)
//
// Exemplos:
//   node codex/blog.mjs ping
//   node codex/blog.mjs news inteligencia artificial --limit 5
//   node codex/blog.mjs publish artigo.json
//   cat artigo.json | node codex/blog.mjs publish

import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

function readEnv() {
  try {
    return Object.fromEntries(
      readFileSync(resolve(process.cwd(), '.env'), 'utf8')
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter((line) => line && !line.startsWith('#') && line.includes('='))
        .map((line) => {
          const index = line.indexOf('=');
          const key = line.slice(0, index).trim();
          const value = line.slice(index + 1).trim().replace(/^['"]|['"]$/g, '');
          return [key, value];
        })
    );
  } catch {
    return {};
  }
}

const env = readEnv();
const BASE = (
  process.env.BLOG_API_URL ||
  env.BLOG_API_URL ||
  env.APP_URL ||
  'http://127.0.0.1:8000'
).replace(/\/+$/, '');
const KEY = process.env.BLOG_API_KEY || env.BLOG_API_KEY || env.CODEX_API_KEY || '';

function fail(message, code = 1) {
  console.error(`erro: ${message}`);
  process.exit(code);
}

function parseFlags(args) {
  const flags = {};
  const positional = [];
  for (let i = 0; i < args.length; i++) {
    const a = args[i];
    if (a.startsWith('--')) {
      const key = a.slice(2);
      const next = args[i + 1];
      if (next === undefined || next.startsWith('--')) {
        flags[key] = true;
      } else {
        flags[key] = next;
        i++;
      }
    } else {
      positional.push(a);
    }
  }
  return { flags, positional };
}

async function api(method, path, body) {
  if (!KEY) fail('defina BLOG_API_KEY (a CODEX_API_KEY configurada no .env do blog).');

  let response;
  try {
    response = await fetch(`${BASE}${path}`, {
      method,
      headers: {
        'X-Api-Key': KEY,
        Accept: 'application/json',
        ...(body ? { 'Content-Type': 'application/json' } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
    });
  } catch (e) {
    fail(`falha de conexao com ${BASE}: ${e.message}`);
  }

  const text = await response.text();
  let data;
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    data = { raw: text };
  }

  if (!response.ok) {
    const msg = data?.message || response.statusText;
    fail(`HTTP ${response.status} em ${method} ${path}: ${msg}\n${JSON.stringify(data, null, 2)}`, 2);
  }

  return data;
}

function out(data) {
  process.stdout.write(JSON.stringify(data, null, 2) + '\n');
}

async function readStdin() {
  const chunks = [];
  for await (const chunk of process.stdin) chunks.push(chunk);
  return Buffer.concat(chunks).toString('utf8');
}

async function main() {
  const [, , command, ...rest] = process.argv;
  const { flags, positional } = parseFlags(rest);

  switch (command) {
    case 'ping':
      return out(await api('GET', '/api/ping'));

    case 'research': {
      const params = new URLSearchParams();
      if (flags.monitor) params.set('monitor', String(flags.monitor));
      if (flags.limit) params.set('limit', String(flags.limit));
      const qs = params.toString();
      return out(await api('GET', `/api/news/digest${qs ? `?${qs}` : ''}`));
    }

    case 'news': {
      const params = new URLSearchParams();
      if (flags.monitor) params.set('monitor', String(flags.monitor));
      else params.set('q', positional.join(' '));
      if (flags.limit) params.set('limit', String(flags.limit));
      if (flags.language) params.set('language', String(flags.language));
      if (flags.country) params.set('country', String(flags.country));
      return out(await api('GET', `/api/news?${params.toString()}`));
    }

    case 'monitors':
      return out(await api('GET', '/api/news/monitors'));

    case 'categories':
      return out(await api('GET', '/api/categories'));

    case 'tags':
      return out(await api('GET', '/api/tags'));

    case 'posts': {
      const params = new URLSearchParams();
      if (flags.status) params.set('status', String(flags.status));
      if (flags.q) params.set('q', String(flags.q));
      const qs = params.toString();
      return out(await api('GET', `/api/posts${qs ? `?${qs}` : ''}`));
    }

    case 'get': {
      const slug = positional[0];
      if (!slug) fail('uso: get <slug>');
      return out(await api('GET', `/api/posts/${encodeURIComponent(slug)}`));
    }

    case 'publish': {
      const file = positional[0];
      const payload = file
        ? await import('node:fs/promises').then((fs) => fs.readFile(file, 'utf8'))
        : await readStdin();
      if (!payload.trim()) fail('corpo vazio: informe um arquivo JSON ou envie por STDIN.');
      let body;
      try {
        body = JSON.parse(payload);
      } catch (e) {
        fail(`JSON invalido: ${e.message}`);
      }
      if (!body.title || !body.content) fail('o JSON precisa ao menos de "title" e "content".');
      return out(await api('POST', '/api/posts', body));
    }

    default:
      process.stderr.write(
        'Comandos: ping | research | news <palavras> | monitors | categories | tags | posts | get <slug> | publish [arquivo.json]\n'
      );
      process.exit(command ? 1 : 0);
  }
}

main();
