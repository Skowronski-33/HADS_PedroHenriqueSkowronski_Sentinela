@extends('layouts.app')

@section('title', 'Equipamentos')

@php
    $corStatus = [
        'Disponível' => 'bg-success',
        'Em Uso' => 'bg-info',
        'Em Manutenção' => 'bg-warning text-dark',
        'Indisponível' => 'bg-secondary',
    ];
@endphp

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Equipamentos</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoEquipamento">
            <i class="bi bi-plus-lg"></i> Novo Equipamento
        </button>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="mb-0 text-muted">
                    Mostrando {{ $equipamentos->count() }} de {{ $equipamentos->total() }} equipamentos
                </p>
                <form method="GET" action="{{ route('equipamentos.index') }}" class="d-flex gap-2">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por nome...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-search"></i></button>
                    @if ($busca)
                        <a href="{{ route('equipamentos.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipamentos as $equipamento)
                        <tr>
                            <td>
                                {{ $equipamento->nome }}
                                @if (!$equipamento->ativo)
                                    <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td>{{ $equipamento->tipoEquipamento->nome }}</td>
                            <td><span class="badge {{ $corStatus[$equipamento->status] ?? 'bg-secondary' }}">{{ $equipamento->status }}</span></td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form method="POST" action="{{ route('equipamentos.status', $equipamento) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" style="width: 150px;" onchange="this.form.submit()">
                                            @foreach ($statusOpcoes as $opcao)
                                                <option value="{{ $opcao }}" {{ $equipamento->status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <a href="{{ route('equipamentos.edit', $equipamento) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                @if ($busca)
                                    Nenhum equipamento encontrado para "{{ $busca }}".
                                @else
                                    Nenhum equipamento cadastrado.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($equipamentos->hasPages())
            <div class="card-body border-top">
                {{ $equipamentos->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalNovoEquipamento" tabindex="-1" aria-labelledby="modalNovoEquipamentoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('equipamentos.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNovoEquipamentoLabel">Novo Equipamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" value="{{ old('nome') }}" placeholder="Ex: Gerador Honda EG5000 #1"
                                   class="form-control @error('nome', 'equipamento') is-invalid @enderror" required autofocus>
                            @error('nome', 'equipamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <input type="text" name="tipo_equipamento" list="listaTiposEquipamento" value="{{ old('tipo_equipamento') }}"
                                   placeholder="Escolha um existente ou digite um novo"
                                   class="form-control @error('tipo_equipamento', 'equipamento') is-invalid @enderror" required>
                            <datalist id="listaTiposEquipamento">
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->nome }}">
                                @endforeach
                            </datalist>
                            <div class="form-text">Digitando um tipo novo, ele é cadastrado automaticamente.</div>
                            @error('tipo_equipamento', 'equipamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" rows="2"
                                      class="form-control auto-resize @error('observacoes', 'equipamento') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;">{{ old('observacoes') }}</textarea>
                            @error('observacoes', 'equipamento')
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

    @if ($errors->equipamento->any())
        <script>
            new bootstrap.Modal(document.getElementById('modalNovoEquipamento')).show();
        </script>
    @endif
@endsection
