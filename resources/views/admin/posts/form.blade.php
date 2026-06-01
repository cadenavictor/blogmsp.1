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
        <input name="title" value="{{ old('title', $post->title) }}" required>
    </label>

    <label class="field">
        <span>Slug</span>
        <input name="slug" value="{{ old('slug', $post->slug) }}" required>
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
        <textarea name="excerpt">{{ old('excerpt', $post->excerpt) }}</textarea>
    </label>

    <label class="field span-2">
        <span>Conteudo</span>
        <textarea name="content" required>{{ old('content', $post->content) }}</textarea>
    </label>

    <label class="field">
        <span>Imagem de capa</span>
        <input name="cover_image_path" value="{{ old('cover_image_path', $post->cover_image_path) }}">
    </label>

    <label class="field">
        <span>Publicado em</span>
        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
    </label>

    <label class="field">
        <span>Agendado para</span>
        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($post->scheduled_at)->format('Y-m-d\TH:i')) }}">
    </label>

    <label class="check-field">
        <input type="hidden" name="featured" value="0">
        <input type="checkbox" name="featured" value="1" @checked((string) old('featured', $post->featured ? '1' : '0') === '1')>
        Destaque
    </label>

    @php
        $checkedTagIds = collect(old('tag_ids', $selectedTagIds))
            ->filter(fn ($tagId) => $tagId !== null && $tagId !== '')
            ->map(fn ($tagId) => (string) $tagId)
            ->all();
    @endphp

    <fieldset class="span-2">
        <legend>Tags</legend>
        <input type="hidden" name="tag_ids[]" value="">
        <div class="check-grid">
            @foreach ($tags as $tag)
                <label class="check-field">
                    <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array((string) $tag->id, $checkedTagIds, true))>
                    {{ $tag->name }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <label class="field">
        <span>Titulo SEO</span>
        <input name="seo_title" value="{{ old('seo_title', $post->seo_title) }}">
    </label>

    <label class="field">
        <span>Canonical URL</span>
        <input name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}">
    </label>

    <label class="field span-2">
        <span>Descricao SEO</span>
        <textarea name="seo_description">{{ old('seo_description', $post->seo_description) }}</textarea>
    </label>

    <label class="field">
        <span>Meta robots</span>
        <input name="meta_robots" value="{{ old('meta_robots', $post->meta_robots) }}">
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
        <span>Resumo AI</span>
        <textarea name="ai_summary">{{ old('ai_summary', $post->ai_summary) }}</textarea>
    </label>
</div>

<button type="submit">Salvar</button>
