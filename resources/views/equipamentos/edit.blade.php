@extends('layouts.app')

@section('title', 'Editar Equipamento')

@section('content')
    <h1 class="mb-4">Editar Equipamento</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('equipamentos.update', $equipamento) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome', $equipamento->nome) }}"
                           class="form-control @error('nome') is-invalid @enderror" required autofocus>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <input type="text" name="tipo_equipamento" list="listaTiposEquipamento"
                           value="{{ old('tipo_equipamento', $equipamento->tipoEquipamento->nome) }}"
                           class="form-control @error('tipo_equipamento') is-invalid @enderror" required>
                    <datalist id="listaTiposEquipamento">
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->nome }}">
                        @endforeach
                    </datalist>
                    <div class="form-text">Digitando um tipo novo, ele é cadastrado automaticamente.</div>
                    @error('tipo_equipamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="ativo" id="ativo" value="1" class="form-check-input"
                           {{ old('ativo', $equipamento->ativo) ? 'checked' : '' }}>
                    <label for="ativo" class="form-check-label">Equipamento ativo</label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="observacoes" rows="3"
                              class="form-control auto-resize @error('observacoes') is-invalid @enderror"
                              style="resize: none; overflow: hidden;">{{ old('observacoes', $equipamento->observacoes) }}</textarea>
                    @error('observacoes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('equipamentos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
