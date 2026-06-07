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
        <input name="name" value="{{ old('name', $snippet->name) }}" required>
    </label>

    <label class="field">
        <span>Provider</span>
        <input name="provider" value="{{ old('provider', $snippet->provider) }}" required>
    </label>

    <label class="field">
        <span>Posicao</span>
        <select name="position" required>
            @foreach ($positions as $position)
                <option value="{{ $position }}" @selected(old('position', $snippet->position) === $position)>{{ $position }}</option>
            @endforeach
        </select>
    </label>

    <label class="check-field">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $snippet->is_active))>
        Ativo
    </label>

    <label class="field">
        <span>Categoria de cookie</span>
        <select name="cookie_category">
            @foreach (($cookieCategories ?? []) as $cookieCategory)
                <option value="{{ $cookieCategory }}" @selected(old('cookie_category', $snippet->cookie_category ?: 'essential') === $cookieCategory)>{{ $cookieCategory }}</option>
            @endforeach
        </select>
    </label>

    <label class="check-field">
        <input type="hidden" name="requires_consent" value="0">
        <input type="checkbox" name="requires_consent" value="1" @checked((bool) old('requires_consent', $snippet->requires_consent))>
        Exigir consentimento LGPD
    </label>

    <label class="field span-2">
        <span>Conteudo</span>
        <textarea name="content" required>{{ old('content', $snippet->content) }}</textarea>
    </label>

    <label class="field span-2">
        <span>Notas</span>
        <textarea name="notes">{{ old('notes', $snippet->notes) }}</textarea>
    </label>
</div>

<button type="submit">Salvar</button>
