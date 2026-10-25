@extends('layouts.app')

@section('title', 'Editar Equipamento')

@section('content')
    <h1 class="page-header-sentinela"><i class="bi bi-pencil-square"></i> Editar Equipamento</h1>

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
                    <div class="combobox" data-options="{{ $tipos->pluck('nome')->toJson() }}">
                        <input type="text" name="tipo_equipamento" autocomplete="off"
                               value="{{ old('tipo_equipamento', $equipamento->tipoEquipamento->nome) }}"
                               class="combobox-input form-control @error('tipo_equipamento') is-invalid @enderror" required>
                        <ul class="combobox-list" hidden></ul>
                    </div>
                    <div class="form-text">Digitando um tipo novo, ele é cadastrado automaticamente.</div>
                    @error('tipo_equipamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Patrimônio/Série <span class="text-muted">(Opcional)</span></label>
                        <input type="text" name="patrimonio" value="{{ old('patrimonio', $equipamento->patrimonio) }}"
                               class="form-control @error('patrimonio') is-invalid @enderror">
                        @error('patrimonio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Localização <span class="text-muted">(Opcional)</span></label>
                        <input type="text" name="localizacao" value="{{ old('localizacao', $equipamento->localizacao) }}"
                               class="form-control @error('localizacao') is-invalid @enderror">
                        @error('localizacao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Última manutenção <span class="text-muted">(Opcional)</span></label>
                    <input type="date" name="data_ultima_manutencao"
                           value="{{ old('data_ultima_manutencao', $equipamento->data_ultima_manutencao?->format('Y-m-d')) }}"
                           class="form-control @error('data_ultima_manutencao') is-invalid @enderror">
                    @error('data_ultima_manutencao')
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
