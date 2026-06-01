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
        <span>Nome do monitoramento</span>
        <input name="name" value="{{ old('name', $monitor->name) }}" data-slug-source required>
    </label>

    <label class="field">
        <span>Slug</span>
        <input name="slug" value="{{ old('slug', $monitor->slug) }}" data-slug-target placeholder="gerado a partir do nome">
    </label>

    <label class="field span-2">
        <span>Palavras-chave</span>
        <input name="keywords" value="{{ old('keywords', $monitor->keywords) }}" placeholder='Ex.: "inteligencia artificial" OR "machine learning"' required>
        <small class="muted">Aceita a sintaxe de busca do Google News (aspas, OR, site:, -excluir).</small>
    </label>

    <label class="field">
        <span>Idioma (hl)</span>
        <input name="language" value="{{ old('language', $monitor->language ?: 'pt-BR') }}" required>
    </label>

    <label class="field">
        <span>Pais (gl)</span>
        <input name="country" value="{{ old('country', $monitor->country ?: 'BR') }}" required>
    </label>

    <label class="field">
        <span>Maximo de resultados</span>
        <input type="number" name="max_results" min="1" max="50" value="{{ old('max_results', $monitor->max_results ?: 20) }}" required>
    </label>

    <label class="check-field">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $monitor->is_active ?? true))>
        Ativo (disponivel na API)
    </label>
</div>

<div class="form-footer">
    <button type="submit">Salvar monitoramento</button>
    <a class="button secondary" href="{{ route('admin.news.index') }}">Cancelar</a>
</div>
