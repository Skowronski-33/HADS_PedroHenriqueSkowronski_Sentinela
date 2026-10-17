@php $isEdit = $abrigo !== null; @endphp

<div class="mb-3">
    <label for="nome" class="form-label">Nome</label>
    <input type="text" name="nome" id="nome"
           class="form-control @error('nome') is-invalid @enderror"
           value="{{ old('nome', $abrigo->nome ?? '') }}" required autofocus>
    @error('nome')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="endereco" class="form-label">Endereço</label>
    <input type="text" name="endereco" id="endereco"
           class="form-control @error('endereco') is-invalid @enderror"
           value="{{ old('endereco', $abrigo->endereco ?? '') }}" required>
    @error('endereco')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="bairro" class="form-label">Bairro</label>
        <input type="text" name="bairro" id="bairro"
               class="form-control @error('bairro') is-invalid @enderror"
               value="{{ old('bairro', $abrigo->bairro ?? '') }}">
        @error('bairro')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="capacidade" class="form-label">Capacidade Máxima (pessoas)</label>
        <input type="number" name="capacidade" id="capacidade" min="1"
               class="form-control @error('capacidade') is-invalid @enderror"
               value="{{ old('capacidade', $abrigo->capacidade ?? '') }}" required>
        @error('capacidade')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="responsavel" class="form-label">Responsável</label>
    <input type="text" name="responsavel" id="responsavel"
           class="form-control @error('responsavel') is-invalid @enderror"
           value="{{ old('responsavel', $abrigo->responsavel ?? '') }}">
    @error('responsavel')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if ($isEdit)
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach (['Ativo', 'Lotado', 'Desativado'] as $opcao)
                    <option value="{{ $opcao }}" {{ old('status', $abrigo->status) === $opcao ? 'selected' : '' }}>
                        {{ $opcao }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3 d-flex align-items-end">
            <div class="form-check">
                <input type="checkbox" name="ativo" id="ativo" value="1" class="form-check-input"
                       {{ old('ativo', $abrigo->ativo) ? 'checked' : '' }}>
                <label for="ativo" class="form-check-label">Abrigo ativo</label>
            </div>
        </div>
    </div>
@endif

<div class="mb-3">
    <label for="observacoes" class="form-label">Observações</label>
    <textarea name="observacoes" id="observacoes" rows="3"
              class="form-control auto-resize @error('observacoes') is-invalid @enderror"
              style="resize: none; overflow: hidden;">{{ old('observacoes', $abrigo->observacoes ?? '') }}</textarea>
    @error('observacoes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
