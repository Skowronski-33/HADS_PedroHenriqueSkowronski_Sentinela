@extends('layouts.app')

@section('title', 'Editar Viatura')

@section('content')
    <h1 class="mb-4">Editar Viatura</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('viaturas.update', $viatura) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Placa</label>
                    <input type="text" name="placa" value="{{ old('placa', $viatura->placa) }}" maxlength="10" style="text-transform: uppercase;"
                           class="form-control @error('placa') is-invalid @enderror" required autofocus>
                    @error('placa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <input type="text" name="identificacao" value="{{ old('identificacao', $viatura->identificacao) }}"
                           class="form-control @error('identificacao') is-invalid @enderror" required>
                    @error('identificacao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Modelo <span class="text-muted">(Opcional)</span></label>
                    <input type="text" name="modelo" value="{{ old('modelo', $viatura->modelo) }}"
                           class="form-control @error('modelo') is-invalid @enderror">
                    @error('modelo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="ativo" id="ativo" value="1" class="form-check-input"
                           {{ old('ativo', $viatura->ativo) ? 'checked' : '' }}>
                    <label for="ativo" class="form-check-label">Viatura ativa</label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control auto-resize @error('observacoes') is-invalid @enderror"
                              style="resize: none; overflow: hidden;">{{ old('observacoes', $viatura->observacoes) }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('viaturas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
