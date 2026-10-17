@extends('layouts.app')

@section('title', 'Viaturas')

@php
    $corStatus = [
        'Disponível' => 'bg-success',
        'Em Atendimento' => 'bg-info',
        'Em Manutenção' => 'bg-warning text-dark',
    ];
@endphp

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Viaturas</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaViatura">
            <i class="bi bi-plus-lg"></i> Nova Viatura
        </button>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="mb-0 text-muted">
                    Mostrando {{ $viaturas->count() }} de {{ $viaturas->total() }} viaturas
                </p>
                <form method="GET" action="{{ route('viaturas.index') }}" class="d-flex gap-2">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por placa ou tipo...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-search"></i></button>
                    @if ($busca)
                        <a href="{{ route('viaturas.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Modelo</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($viaturas as $viatura)
                        <tr>
                            <td>
                                {{ $viatura->placa }}
                                @if (!$viatura->ativo)
                                    <span class="badge bg-secondary">Inativa</span>
                                @endif
                            </td>
                            <td>{{ $viatura->identificacao }}</td>
                            <td>{{ $viatura->modelo ?: '-' }}</td>
                            <td><span class="badge {{ $corStatus[$viatura->status] ?? 'bg-secondary' }}">{{ $viatura->status }}</span></td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form method="POST" action="{{ route('viaturas.status', $viatura) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                                            @foreach ($statusOpcoes as $opcao)
                                                <option value="{{ $opcao }}" {{ $viatura->status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <a href="{{ route('viaturas.edit', $viatura) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                @if ($busca)
                                    Nenhuma viatura encontrada para "{{ $busca }}".
                                @else
                                    Nenhuma viatura cadastrada.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($viaturas->hasPages())
            <div class="card-body border-top">
                {{ $viaturas->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalNovaViatura" tabindex="-1" aria-labelledby="modalNovaViaturaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('viaturas.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNovaViaturaLabel">Nova Viatura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Placa</label>
                            <input type="text" name="placa" value="{{ old('placa') }}" placeholder="ABC1D23" maxlength="10" style="text-transform: uppercase;"
                                   class="form-control @error('placa', 'viatura') is-invalid @enderror" required autofocus>
                            @error('placa', 'viatura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <input type="text" name="identificacao" value="{{ old('identificacao') }}" placeholder="Ex: Ambulância, Caminhão-pipa"
                                   class="form-control @error('identificacao', 'viatura') is-invalid @enderror" required>
                            @error('identificacao', 'viatura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modelo <span class="text-muted">(Opcional)</span></label>
                            <input type="text" name="modelo" value="{{ old('modelo') }}" placeholder="Ex: Mercedes Sprinter"
                                   class="form-control @error('modelo', 'viatura') is-invalid @enderror">
                            @error('modelo', 'viatura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" rows="2"
                                      class="form-control auto-resize @error('observacoes', 'viatura') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;">{{ old('observacoes') }}</textarea>
                            @error('observacoes', 'viatura')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->viatura->any())
        <script>
            new bootstrap.Modal(document.getElementById('modalNovaViatura')).show();
        </script>
    @endif
@endsection
