# Rotina diaria de posts automaticos

Objetivo: publicar 1 post por dia no Melhores de Sao Paulo a partir dos monitoramentos ativos do Google News, usando a API do proprio blog.

## Modo de publicacao

- Publicar diretamente com `status: "published"`.
- Nao criar rascunho, exceto se houver risco editorial claro, falta de fonte confiavel ou noticia irrelevante.
- Se nao houver noticia adequada, nao publicar.

## Passo a passo obrigatorio

1. Testar a API:
   ```bash
   node codex/blog.mjs ping
   ```
2. Gerar o briefing diario:
   ```bash
   node codex/daily-news-brief.mjs --limit 6 --recent 30
   ```
3. Ler todos os monitoramentos e noticias retornadas.
4. Rejeitar noticias que:
   - nao tenham relacao clara com empresas, servicos, consumo, experiencias, bairros ou mercado local de Sao Paulo;
   - sejam apenas opiniao, fofoca, clickbait ou republicacao sem fato novo;
   - repitam tema ja publicado recentemente;
   - dependam de informacao que nao esteja nas fontes retornadas.
5. Escolher a noticia com maior valor editorial para o publico do blog.
6. Escrever um artigo original em HTML semantico, sem copiar texto das fontes.
7. Citar as fontes no corpo com links `<a href="...">`.
8. Publicar com:
   ```bash
   node codex/blog.mjs publish artigo.json
   ```
9. Conferir a URL retornada.

## Criterios de relevancia

Priorize noticias que ajudem o leitor a decidir melhor sobre:

- empresas e servicos em Sao Paulo;
- novos restaurantes, clinicas, escolas, academias, imobiliarias, coworkings, lojas, apps ou servicos locais;
- rankings, premios, fiscalizacoes, mudancas regulatorias, expansoes e fechamentos;
- tendencias locais que impactem consumo, mobilidade, moradia, lazer, saude, educacao ou negocios.

## Estrutura do artigo

O JSON publicado deve conter:

```json
{
  "title": "Titulo claro com entidade e Sao Paulo quando fizer sentido",
  "content": "<p>Resposta direta...</p><h2>...</h2><p>...</p>",
  "excerpt": "Resumo de 1-2 frases.",
  "category": "Noticias",
  "tags": ["Sao Paulo", "Empresas", "Servicos"],
  "status": "published",
  "ai_summary": "Resumo factual em 2-3 frases para agentes de IA.",
  "key_takeaways": [
    "Conclusao objetiva 1.",
    "Conclusao objetiva 2.",
    "Conclusao objetiva 3."
  ],
  "entities": ["Sao Paulo", "Nome da empresa", "Nome do bairro"],
  "faq_items": [
    {
      "question": "O que aconteceu?",
      "answer": "Resposta completa e objetiva."
    }
  ],
  "seo_title": "Titulo SEO com ate 70 caracteres",
  "seo_description": "Descricao SEO com ate 160 caracteres"
}
```

## Regras de qualidade SEO/GEO

- Comece com um paragrafo que responda diretamente o que aconteceu e por que importa.
- Use `<h2>` e `<h3>` para perguntas e subtópicos que agentes de IA possam citar.
- Inclua 3 a 6 `key_takeaways` auto-contidos.
- Inclua 2 a 5 perguntas frequentes reais.
- Use entidades relevantes: empresas, pessoas, orgaos, bairros, cidade, setor.
- Nao invente numeros, datas, nomes, valores ou declaracoes.
- Quando houver incerteza, escreva de forma conservadora e atribua a fonte.
- Nao use linguagem promocional.
- Nao publique conteudo duplicado.

## Saida esperada

Ao final da rotina, registrar:

- monitoramento usado;
- noticia escolhida;
- fontes consultadas;
- titulo publicado;
- URL retornada pela API;
- motivo da escolha editorial.
