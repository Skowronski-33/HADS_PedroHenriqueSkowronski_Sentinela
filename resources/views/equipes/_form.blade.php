@php
    $isEdit = $equipe !== null;
    $membrosAtuais = $isEdit ? $equipe->voluntarios->pluck('id')->toArray() : old('voluntarios', []);
@endphp

<div class="form-section-title"><i class="bi bi-people-fill"></i> Identificação</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="nome" class="form-label">Nome da equipe</label>
        <input type="text" name="nome" id="nome"
               class="form-control @error('nome') is-invalid @enderror"
               value="{{ old('nome', $equipe->nome ?? '') }}" required autofocus>
        @error('nome')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="tipo_equipe_id" class="form-label">Tipo</label>
        <select name="tipo_equipe_id" id="tipo_equipe_id" class="form-select @error('tipo_equipe_id') is-invalid @enderror" required>
            <option value="">Selecione...</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo->id }}" {{ (int) old('tipo_equipe_id', $equipe->tipo_equipe_id ?? 0) === $tipo->id ? 'selected' : '' }}>
                    {{ $tipo->nome }}
                </option>
            @endforeach
        </select>
        @error('tipo_equipe_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@if ($isEdit)
    <div class="form-check mb-3">
        <input type="checkbox" name="ativo" id="ativo" value="1" class="form-check-input"
               {{ old('ativo', $equipe->ativo) ? 'checked' : '' }}>
        <label for="ativo" class="form-check-label">Equipe ativa</label>
    </div>
@endif

<div class="form-section-title"><i class="bi bi-person-check"></i> Membros</div>

<div class="mb-3">
    @if ($voluntarios->isEmpty())
        <p class="text-muted small mb-0">Nenhum voluntário ativo disponível para compor a equipe.</p>
    @else
        <div class="row">
            @foreach ($voluntarios as $voluntario)
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" name="voluntarios[]" value="{{ $voluntario->id }}"
                               id="voluntario_{{ $voluntario->id }}" class="form-check-input"
                               {{ in_array($voluntario->id, $membrosAtuais) ? 'checked' : '' }}>
                        <label for="voluntario_{{ $voluntario->id }}" class="form-check-label">
                            {{ $voluntario->nome }}
                            @if ($voluntario->telefone)
                                <span class="text-muted small">— {{ $voluntario->telefone }}</span>
                            @endif
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="form-section-title"><i class="bi bi-journal-text"></i> Observações</div>

<div class="mb-3">
    <textarea name="observacoes" id="observacoes" rows="3"
              class="form-control auto-resize @error('observacoes') is-invalid @enderror"
              style="resize: none; overflow: hidden;">{{ old('observacoes', $equipe->observacoes ?? '') }}</textarea>
    @error('observacoes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
