@if ($errors->any())
    <div class="alert error" role="alert">
        <strong>Revise os campos destacados.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-grid">
    <label class="field">
        <span>Titulo</span>
        <input name="title" value="{{ old('title', $post->title) }}" data-slug-source required>
    </label>

    <label class="field">
        <span>Slug</span>
        <input name="slug" value="{{ old('slug', $post->slug) }}" data-slug-target required>
    </label>

    <label class="field">
        <span>Categoria</span>
        <select name="category_id" required>
            <option value="">Selecione</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="field">
        <span>Status</span>
        <select name="status" required>
            @foreach (['draft' => 'Rascunho', 'published' => 'Publicado', 'scheduled' => 'Agendado'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $post->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="field span-2">
        <span>Resumo</span>
        <textarea name="excerpt" placeholder="Resumo curto exibido em listagens e usado como meta description padrao.">{{ old('excerpt', $post->excerpt) }}</textarea>
    </label>

    <div class="field span-2">
        <span>Conteudo</span>
        <div data-editor>
            <textarea name="content" data-editor-source data-placeholder="Escreva o conteudo do post..." required>{{ old('content', $post->content) }}</textarea>
        </div>
    </div>

    <div class="field">
        <span>Imagem de capa</span>
        <div class="uploader" data-uploader>
            @php $cover = old('cover_image_path', $post->cover_image_path); @endphp
            <div class="preview" data-uploader-preview>
                @if ($cover)
                    <img src="{{ $cover }}" alt="Pre-visualizacao da capa">
                @else
                    <span class="placeholder">Nenhuma imagem selecionada</span>
                @endif
            </div>
            <input type="text" name="cover_image_path" value="{{ $cover }}" data-uploader-value placeholder="URL/caminho da imagem ou envie um arquivo">
            <div class="uploader-actions">
                <label class="button secondary">
                    Enviar imagem
                    <input type="file" accept="image/*" hidden>
                </label>
                <button type="button" class="ghost" data-uploader-clear>Limpar</button>
            </div>
        </div>
    </div>

    <div class="field">
        <label class="field">
            <span>Publicado em</span>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
        </label>
        <label class="field" style="margin-top:14px">
            <span>Agendado para</span>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($post->scheduled_at)->format('Y-m-d\TH:i')) }}">
        </label>
        <label class="check-field" style="margin-top:14px">
            <input type="hidden" name="featured" value="0">
            <input type="checkbox" name="featured" value="1" @checked((string) old('featured', $post->featured ? '1' : '0') === '1')>
            Destaque na home
        </label>
    </div>

    @php
        $checkedTagIds = collect(old('tag_ids', $selectedTagIds))
            ->filter(fn ($tagId) => $tagId !== null && $tagId !== '')
            ->map(fn ($tagId) => (string) $tagId)
            ->all();
    @endphp

    <fieldset class="span-2">
        <legend>Tags</legend>
        <input type="hidden" name="tag_ids[]" value="">
        @if ($tags->isEmpty())
            <p class="muted">Nenhuma tag cadastrada. <a href="{{ route('admin.tags.create') }}">Crie tags</a> para classificar os posts.</p>
        @else
            <div class="check-grid">
                @foreach ($tags as $tag)
                    <label class="check-field">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array((string) $tag->id, $checkedTagIds, true))>
                        {{ $tag->name }}
                    </label>
                @endforeach
            </div>
        @endif
    </fieldset>

    <details class="collapse" @if ($errors->hasAny(['seo_title', 'seo_description', 'canonical_url', 'meta_robots', 'og_title', 'og_description', 'ai_summary'])) open @endif>
        <summary>SEO, Open Graph &amp; GEO (opcional)</summary>
        <div class="collapse-body">
            <label class="field">
                <span>Titulo SEO</span>
                <input name="seo_title" value="{{ old('seo_title', $post->seo_title) }}" placeholder="Padrao: titulo do post">
            </label>

            <label class="field">
                <span>Canonical URL</span>
                <input name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}">
            </label>

            <label class="field span-2">
                <span>Descricao SEO</span>
                <textarea name="seo_description" placeholder="Padrao: resumo do post (ate ~160 caracteres)">{{ old('seo_description', $post->seo_description) }}</textarea>
            </label>

            <label class="field">
                <span>Meta robots</span>
                <input name="meta_robots" value="{{ old('meta_robots', $post->meta_robots) }}" placeholder="index, follow">
            </label>

            <label class="field">
                <span>Titulo OG</span>
                <input name="og_title" value="{{ old('og_title', $post->og_title) }}">
            </label>

            <label class="field span-2">
                <span>Descricao OG</span>
                <textarea name="og_description">{{ old('og_description', $post->og_description) }}</textarea>
            </label>

            <label class="field span-2">
                <span>Resumo para IA (ai_summary)</span>
                <textarea name="ai_summary" placeholder="Resumo objetivo para agentes/LLMs. Os campos GEO em lista (takeaways, entidades, FAQ) tambem podem ser preenchidos via API.">{{ old('ai_summary', $post->ai_summary) }}</textarea>
            </label>
        </div>
    </details>
</div>

<div class="form-footer">
    <button type="submit">Salvar post</button>
    <a class="button secondary" href="{{ route('admin.posts.index') }}">Cancelar</a>
</div>
