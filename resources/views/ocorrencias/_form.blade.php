@php $isEdit = $ocorrencia !== null; @endphp

<div class="form-section-title"><i class="bi bi-exclamation-triangle"></i> Classificação</div>

<div class="mb-3">
    <label for="tipo_ocorrencia_id" class="form-label">Tipo de ocorrência</label>
    <select name="tipo_ocorrencia_id" id="tipo_ocorrencia_id" class="form-select @error('tipo_ocorrencia_id') is-invalid @enderror" required>
        <option value="">Selecione...</option>
        @foreach ($tipos as $tipo)
            <option value="{{ $tipo->id }}" {{ (int) old('tipo_ocorrencia_id', $ocorrencia->tipo_ocorrencia_id ?? 0) === $tipo->id ? 'selected' : '' }}>
                {{ $tipo->nome }}
            </option>
        @endforeach
    </select>
    @error('tipo_ocorrencia_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if (! $isEdit)
        <div class="form-text">A prioridade é definida depois, na etapa de classificação (após a triagem).</div>
    @endif
</div>

<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea name="descricao" id="descricao" rows="3"
              class="form-control auto-resize @error('descricao') is-invalid @enderror"
              style="resize: none; overflow: hidden;" required>{{ old('descricao', $ocorrencia->descricao ?? '') }}</textarea>
    @error('descricao')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-section-title"><i class="bi bi-geo-alt"></i> Local</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="endereco" class="form-label">Endereço</label>
        <input type="text" name="endereco" id="endereco"
               class="form-control @error('endereco') is-invalid @enderror"
               value="{{ old('endereco', $ocorrencia->endereco ?? '') }}" required>
        @error('endereco')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="bairro" class="form-label">Bairro</label>
        <input type="text" name="bairro" id="bairro"
               class="form-control @error('bairro') is-invalid @enderror"
               value="{{ old('bairro', $ocorrencia->bairro ?? '') }}">
        @error('bairro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="cidade" class="form-label">Cidade</label>
        <input type="text" name="cidade" id="cidade"
               class="form-control @error('cidade') is-invalid @enderror"
               value="{{ old('cidade', $ocorrencia->cidade ?? '') }}">
        @error('cidade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="ponto_referencia" class="form-label">Ponto de referência <span class="text-muted">(Opcional)</span></label>
        <input type="text" name="ponto_referencia" id="ponto_referencia"
               class="form-control @error('ponto_referencia') is-invalid @enderror"
               value="{{ old('ponto_referencia', $ocorrencia->ponto_referencia ?? '') }}">
        @error('ponto_referencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-section-title"><i class="bi bi-clock-history"></i> Data e Observações</div>

<div class="mb-3">
    <label for="data_hora" class="form-label">Data/hora do registro</label>
    <input type="datetime-local" name="data_hora" id="data_hora"
           class="form-control @error('data_hora') is-invalid @enderror"
           value="{{ old('data_hora', isset($ocorrencia) && $ocorrencia ? $ocorrencia->data_hora->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
           {{ $isEdit ? 'required' : '' }}>
    @error('data_hora')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="observacoes" class="form-label">Observações <span class="text-muted">(Opcional)</span></label>
    <textarea name="observacoes" id="observacoes" rows="2"
              class="form-control auto-resize @error('observacoes') is-invalid @enderror"
              style="resize: none; overflow: hidden;">{{ old('observacoes', $ocorrencia->observacoes ?? '') }}</textarea>
    @error('observacoes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
