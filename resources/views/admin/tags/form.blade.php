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
        <input name="name" value="{{ old('name', $tag->name) }}" data-slug-source required>
    </label>

    <label class="field">
        <span>Slug</span>
        <input name="slug" value="{{ old('slug', $tag->slug) }}" data-slug-target placeholder="gerado a partir do nome">
    </label>

    <label class="field span-2">
        <span>Descricao curta</span>
        <textarea name="description">{{ old('description', $tag->description) }}</textarea>
    </label>
</div>

<div class="form-footer">
    <button type="submit">Salvar tag</button>
    <a class="button secondary" href="{{ route('admin.tags.index') }}">Cancelar</a>
</div>
