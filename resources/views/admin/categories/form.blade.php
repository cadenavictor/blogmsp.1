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
        <span>Nome</span>
        <input name="name" value="{{ old('name', $category->name) }}" data-slug-source required>
    </label>

    <label class="field">
        <span>Slug</span>
        <input name="slug" value="{{ old('slug', $category->slug) }}" data-slug-target placeholder="gerado a partir do nome">
    </label>

    <label class="field span-2">
        <span>Descricao</span>
        <textarea name="description">{{ old('description', $category->description) }}</textarea>
    </label>

    <label class="field">
        <span>Titulo SEO</span>
        <input name="seo_title" value="{{ old('seo_title', $category->seo_title) }}">
    </label>

    <label class="field">
        <span>Descricao SEO</span>
        <input name="seo_description" value="{{ old('seo_description', $category->seo_description) }}">
    </label>
</div>

<div class="form-footer">
    <button type="submit">Salvar categoria</button>
    <a class="button secondary" href="{{ route('admin.categories.index') }}">Cancelar</a>
</div>
