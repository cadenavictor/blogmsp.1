# Blog SEO/GEO Design

## Objetivo

Criar uma plataforma editorial em Laravel 13 com Blade, JavaScript leve e MySQL local pelo Laragon. O sistema deve publicar um blog otimizado para SEO tradicional, descoberta por agentes de IA, GEO e indexacao, com painel administrativo fechado, CRUD editorial e ferramentas tecnicas de distribuicao.

## Escopo Aprovado

O escopo escolhido e a opcao C, com Web Stories preparado para uma fase futura. O primeiro ciclo entrega o blog publico, painel administrativo, CRUD editorial, SEO/GEO por postagem, scripts/Search Console, sitemap estruturado, feeds, robots.txt, llms.txt e IndexNow. Nao sera criado um editor de Web Stories neste ciclo.

## Base Tecnica

- Laravel 13, Blade, Vite, Tailwind e JavaScript sem SPA.
- MySQL em `127.0.0.1:3306` pelo Laragon.
- Autenticacao por starter kit oficial/Fortify, com registro publico desabilitado.
- Usuarios administrativos criados por seeder e, depois, gerenciaveis pelo painel.
- Views server-rendered para garantir conteudo textual renderizado no HTML inicial.

## Modelo Editorial

### Postagens

Cada postagem tera titulo, slug, resumo, conteudo, status, imagem de capa, autor, categoria, tags, datas de publicacao e atualizacao, destaque e campos SEO/GEO. Os status iniciais serao rascunho, publicado e agendado.

### Categorias e Tags

Categorias terao nome, slug, descricao e metadados SEO. Tags terao nome, slug e descricao curta. As listagens publicas de categorias e tags devem ser indexaveis quando tiverem conteudo publicado.

### Autores

Autores serao usuarios autenticados com perfil publico: nome, bio, avatar e links. O avatar podera ficar vazio e, nesse caso, a interface exibira iniciais do autor. Posts publicados devem apontar para pagina autoral para reforcar autoria e confianca.

## Experiencia Publica

- Home com posts recentes, destaques, categorias principais e busca.
- Listagem de posts com paginacao, filtros por categoria/tag e busca textual.
- Pagina de postagem com HTML semantico, sumario gerado quando houver dois ou mais subtitulos, breadcrumbs, links internos e posts relacionados.
- Paginas de categoria, tag, autor, politica de privacidade basica e contato editorial.
- Layout responsivo, rapido e acessivel.

## Painel Administrativo

O painel ficara em rotas protegidas por autenticacao. A navegacao inicial tera dashboard, postagens, categorias, tags, scripts, SEO/indexacao e configuracoes.

O dashboard exibira contadores de posts por status, ultimas postagens, URLs aguardando envio IndexNow, alertas de SEO e links para arquivos publicos como sitemap, robots.txt e llms.txt.

O CRUD de postagens deve incluir busca, filtros por status/categoria/tag, ordenacao por data, criacao, edicao, publicacao, agendamento e exclusao. O editor inicial sera textarea para HTML controlado, com campos auxiliares para resumo, destaques, entidades e perguntas frequentes.

## SEO por Pagina e Postagem

Cada postagem e pagina indexavel devera expor:

- `title`, `meta description`, canonical e meta robots.
- Open Graph e Twitter Card.
- `datePublished`, `dateModified`, autor e imagem.
- JSON-LD `BlogPosting` ou `Article`.
- JSON-LD `BreadcrumbList`.
- Campos opcionais para FAQ simples quando houver perguntas e respostas visiveis no conteudo.
- URLs absolutas baseadas em `APP_URL`.

O painel deve oferecer checklist editorial com sinais como titulo SEO preenchido, descricao adequada, slug legivel, resumo para IA, imagem de capa, categoria, pelo menos uma tag, links internos e perguntas respondidas.

## GEO e Agentes de IA

O conteudo deve ser facil de citar e resumir por agentes:

- HTML server-rendered com conteudo principal em texto.
- Resumo curto da postagem e campo `ai_summary`.
- Campo `key_takeaways` para conclusoes objetivas.
- Campo `entities` para nomes, marcas, produtos, locais ou conceitos importantes.
- Perguntas e respostas visiveis quando usadas em schema FAQ.
- `llms.txt` na raiz com resumo do site, principais secoes, politicas de uso e URLs de alto valor.
- Politica configuravel para bots de IA em `robots.txt`, com padrao de maxima descoberta: permitir OAI-SearchBot, PerplexityBot, ClaudeBot, Googlebot e Bingbot; permitir GPTBot e Google-Extended por padrao, mas oferecer controles para bloquear treinamento sem bloquear crawlers de busca.

Observacao de pesquisa: a documentacao do Google informa que as boas praticas de SEO continuam relevantes para recursos de IA da Pesquisa e que nao ha requisito especial de arquivos de IA ou schema extra para aparecer nesses recursos. Portanto, `llms.txt` e regras especificas para crawlers serao tratados como complemento para agentes e plataformas fora do fluxo tradicional do Google.

## Scripts, Verificacoes e Search Console

Criar area administrativa para scripts e verificacoes com:

- Nome, provedor, posicao (`head_start`, `head_end`, `body_start`, `body_end`), conteudo, status ativo/inativo e observacao.
- Tipos sugeridos: Search Console meta verification, Google Analytics, Tag Manager, pixels e scripts personalizados.
- Renderizacao somente de scripts ativos.
- Aviso no painel para scripts potencialmente perigosos, porque o conteudo sera inserido no HTML.

## Sitemaps, Feeds e Robots

O sitemap sera um indice em `/sitemap.xml`, apontando para:

- `/sitemap-posts.xml`
- `/sitemap-categories.xml`
- `/sitemap-tags.xml`
- `/sitemap-pages.xml`

Cada URL deve incluir `loc` e `lastmod`. Posts agendados, rascunhos e paginas nao indexaveis nao entram no sitemap.

Tambem serao criados:

- `/feed.xml` com posts publicados recentes.
- `/robots.txt` dinamico com `Sitemap: {APP_URL}/sitemap.xml`, bloqueio do painel administrativo e regras configuraveis para crawlers.
- `/llms.txt` dinamico.

## IndexNow

Criar configuracao para IndexNow com chave, host e endpoint. O sistema deve permitir:

- Gerar/armazenar chave.
- Servir a chave em `/{key}.txt`.
- Enviar URLs manualmente pelo painel.
- Registrar tentativas de envio com URL, status HTTP, resposta e data.
- Preparar gancho para envio automatico quando uma postagem publicada for criada ou atualizada.

## Web Stories Futuro

O primeiro ciclo reservara estrutura conceitual para Web Stories sem implementar editor. O menu e as migrations principais nao precisam ser criados agora, mas a arquitetura deve evitar URLs e modelos que dificultem adicionar `/stories/{slug}` depois.

## Testes e Validacao

Usar TDD para comportamento novo. Cobrir:

- Rotas publicas principais.
- Protecao do painel administrativo.
- CRUD editorial basico.
- Renderizacao de meta tags e JSON-LD em posts.
- Sitemap, robots.txt, llms.txt e feed.
- Scripts ativos nas posicoes corretas.
- Registro de envios IndexNow com HTTP fake.

Validacao manual no navegador:

- Home, listagem e post em desktop/mobile.
- Painel administrativo.
- Arquivos `/sitemap.xml`, `/robots.txt`, `/llms.txt` e `/feed.xml`.

## Referencias Pesquisadas

- Google Search Central: recursos de IA e seu site.
- Google Search Central: robots.txt.
- Google Search Central: structured data gallery e Article.
- Google Search Central: Web Stories.
- OpenAI Platform: OAI-SearchBot e GPTBot.
- IndexNow: protocolo oficial.
- llms.txt: proposta de arquivo para agentes.
