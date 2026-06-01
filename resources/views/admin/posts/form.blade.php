@if ($errors->any())
    <div role="alert">
        {{ $errors->first() }}
    </div>
@endif

<label>
    Titulo
    <input name="title" value="{{ old('title', $post->title) }}" required>
</label>

<label>
    Slug
    <input name="slug" value="{{ old('slug', $post->slug) }}" required>
</label>

<label>
    Categoria
    <select name="category_id" required>
        <option value="">Selecione</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</label>

<label>
    Status
    <select name="status" required>
        @foreach (['draft' => 'Rascunho', 'published' => 'Publicado', 'scheduled' => 'Agendado'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $post->status) === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>

<label>
    Resumo
    <textarea name="excerpt">{{ old('excerpt', $post->excerpt) }}</textarea>
</label>

<label>
    Conteudo
    <textarea name="content" required>{{ old('content', $post->content) }}</textarea>
</label>

<label>
    Imagem de capa
    <input name="cover_image_path" value="{{ old('cover_image_path', $post->cover_image_path) }}">
</label>

<label>
    <input type="checkbox" name="featured" value="1" @checked(old('featured', $post->featured))>
    Destaque
</label>

<label>
    Publicado em
    <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
</label>

<label>
    Agendado para
    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($post->scheduled_at)->format('Y-m-d\TH:i')) }}">
</label>

<fieldset>
    <legend>Tags</legend>
    <input type="hidden" name="tag_ids[]" value="">
    @foreach ($tags as $tag)
        <label>
            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, old('tag_ids', $selectedTagIds), true))>
            {{ $tag->name }}
        </label>
    @endforeach
</fieldset>

<label>
    Titulo SEO
    <input name="seo_title" value="{{ old('seo_title', $post->seo_title) }}">
</label>

<label>
    Descricao SEO
    <textarea name="seo_description">{{ old('seo_description', $post->seo_description) }}</textarea>
</label>

<label>
    Canonical URL
    <input name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}">
</label>

<label>
    Meta robots
    <input name="meta_robots" value="{{ old('meta_robots', $post->meta_robots) }}">
</label>

<label>
    Titulo OG
    <input name="og_title" value="{{ old('og_title', $post->og_title) }}">
</label>

<label>
    Descricao OG
    <textarea name="og_description">{{ old('og_description', $post->og_description) }}</textarea>
</label>

<label>
    Resumo AI
    <textarea name="ai_summary">{{ old('ai_summary', $post->ai_summary) }}</textarea>
</label>

<button type="submit">Salvar</button>
