#!/usr/bin/env node
// Gera um briefing editorial diario para o Codex escolher uma noticia,
// escrever um artigo original e publicar via API do blog.

import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const env = readEnv();
const BASE = (
  process.env.BLOG_API_URL ||
  env.BLOG_API_URL ||
  env.APP_URL ||
  'http://127.0.0.1:8000'
).replace(/\/+$/, '');
const KEY = process.env.BLOG_API_KEY || env.BLOG_API_KEY || env.CODEX_API_KEY || '';

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

function fail(message, code = 1) {
  console.error(`erro: ${message}`);
  process.exit(code);
}

function parseFlags(args) {
  const flags = {};

  for (let i = 0; i < args.length; i++) {
    const arg = args[i];

    if (!arg.startsWith('--')) continue;

    const key = arg.slice(2);
    const next = args[i + 1];
    flags[key] = next === undefined || next.startsWith('--') ? true : next;

    if (flags[key] === next) i++;
  }

  return flags;
}

async function api(path) {
  if (!KEY) {
    fail('defina BLOG_API_KEY ou CODEX_API_KEY no .env antes de rodar a rotina.');
  }

  const response = await fetch(`${BASE}${path}`, {
    headers: {
      Accept: 'application/json',
      'X-Api-Key': KEY,
    },
  });

  const text = await response.text();
  const data = text ? JSON.parse(text) : {};

  if (!response.ok) {
    fail(`HTTP ${response.status} em ${path}: ${data.message || response.statusText}`, 2);
  }

  return data;
}

function itemScore(item, monitor) {
  const haystack = `${item.title || ''} ${item.source || ''} ${monitor.name || ''} ${monitor.keywords || ''}`.toLowerCase();
  let score = 0;

  for (const term of [
    'sao paulo',
    'são paulo',
    'sp',
    'paulista',
    'empresa',
    'servico',
    'serviço',
    'restaurante',
    'loja',
    'bairro',
    'ranking',
    'inaugura',
    'expansao',
    'expansão',
    'cliente',
    'consumidor',
  ]) {
    if (haystack.includes(term)) score += 1;
  }

  return score;
}

async function main() {
  const flags = parseFlags(process.argv.slice(2));
  const limit = Math.max(1, Math.min(Number(flags.limit || 6), 20));
  const recent = Math.max(1, Math.min(Number(flags.recent || 30), 100));

  const research = await api(`/api/news/digest?limit=${limit}`);
  const posts = await api(`/api/posts?status=published&per_page=${recent}`);
  const categories = await api('/api/categories');
  const tags = await api('/api/tags');

  const candidates = [];

  for (const group of research.data || []) {
    for (const item of group.items || []) {
      candidates.push({
        monitor: group.monitor,
        item,
        score: itemScore(item, group.monitor),
      });
    }
  }

  candidates.sort((a, b) => b.score - a.score);

  process.stdout.write(JSON.stringify({
    mode: 'published',
    generated_at: new Date().toISOString(),
    base_url: BASE,
    instructions: 'Use codex/prompts/daily-news-post.md. Escolha 1 noticia relevante, escreva artigo original e publique com status published.',
    commands: {
      research: `node codex/blog.mjs research --limit ${limit}`,
      publish: 'node codex/blog.mjs publish artigo.json',
    },
    selection_criteria: [
      'relevancia para empresas, servicos e experiencias em Sao Paulo',
      'fato novo e fonte identificavel',
      'potencial de responder perguntas de busca e agentes de IA',
      'nao duplicar posts recentes',
    ],
    recent_posts: posts.data || [],
    categories: categories.data || [],
    tags: tags.data || [],
    candidates: candidates.slice(0, 20),
    research: research.data || [],
  }, null, 2));
  process.stdout.write('\n');
}

main().catch((error) => fail(error.message));
