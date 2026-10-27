@extends('layouts.app')

@section('title', 'Editar Voluntário')

@section('content')
    <h1 class="page-header-sentinela"><i class="bi bi-pencil-square"></i> Editar Voluntário</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('voluntarios.update', $voluntario) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome', $voluntario->nome) }}"
                           class="form-control @error('nome') is-invalid @enderror" required autofocus>
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone <span class="text-muted">(Opcional)</span></label>
                        <input type="text" name="telefone" value="{{ old('telefone', $voluntario->telefone) }}"
                               class="form-control mask-telefone @error('telefone') is-invalid @enderror">
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Habilidade <span class="text-muted">(Opcional)</span></label>
                        <input type="text" name="habilidade" value="{{ old('habilidade', $voluntario->habilidade) }}" placeholder="Ex: Primeiros socorros"
                               class="form-control @error('habilidade') is-invalid @enderror">
                        @error('habilidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('voluntarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
