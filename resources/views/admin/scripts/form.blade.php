@if ($errors->any())
    <div role="alert">
        {{ $errors->first() }}
    </div>
@endif

<label>
    Nome
    <input name="name" value="{{ old('name', $snippet->name) }}" required>
</label>

<label>
    Provider
    <input name="provider" value="{{ old('provider', $snippet->provider) }}" required>
</label>

<label>
    Posicao
    <select name="position" required>
        @foreach ($positions as $position)
            <option value="{{ $position }}" @selected(old('position', $snippet->position) === $position)>{{ $position }}</option>
        @endforeach
    </select>
</label>

<label>
    Conteudo
    <textarea name="content" required>{{ old('content', $snippet->content) }}</textarea>
</label>

<label>
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $snippet->is_active))>
    Ativo
</label>

<label>
    Notas
    <textarea name="notes">{{ old('notes', $snippet->notes) }}</textarea>
</label>

<button type="submit">Salvar</button>
