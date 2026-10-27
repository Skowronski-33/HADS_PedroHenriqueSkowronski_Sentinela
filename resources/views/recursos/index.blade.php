@extends('layouts.app')

@section('title', 'Consultar Recursos')

@php
    $statusSlug = [
        'Disponível' => 'disponivel',
        'Em Uso' => 'uso',
        'Em Atendimento' => 'atendimento',
        'Em Manutenção' => 'manutencao',
        'Indisponível' => 'indisponivel',
    ];
@endphp

@section('content')
    <div class="page-header-sentinela">
        <h1 class="mb-0">Consultar Recursos</h1>
        <p class="text-muted mb-0">Visão geral de equipamentos e viaturas para apoiar o planejamento da operação.</p>
    </div>

    <form method="GET" action="{{ route('recursos.index') }}" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="btn-group" role="group">
            <a href="{{ route('recursos.index', ['busca' => $busca]) }}"
               class="btn btn-sm {{ !$tipo ? 'btn-primary' : 'btn-outline-secondary' }}">Todos</a>
            <a href="{{ route('recursos.index', ['busca' => $busca, 'tipo' => 'Equipamento']) }}"
               class="btn btn-sm {{ $tipo === 'Equipamento' ? 'btn-primary' : 'btn-outline-secondary' }}">Equipamentos</a>
            <a href="{{ route('recursos.index', ['busca' => $busca, 'tipo' => 'Viatura']) }}"
               class="btn btn-sm {{ $tipo === 'Viatura' ? 'btn-primary' : 'btn-outline-secondary' }}">Viaturas</a>
        </div>
        <div class="search-inline" style="width: 300px; max-width: 100%;">
            <i class="bi bi-search"></i>
            <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Buscar por nome...">
            <input type="hidden" name="tipo" value="{{ $tipo }}">
        </div>
    </form>

    @if ($recursos->isEmpty())
        <div class="equip-card">
            <div class="text-center text-muted py-5">
                <i class="bi bi-search" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                <p class="mt-2 mb-0">
                    @if ($busca)
                        Nenhum recurso encontrado para "{{ $busca }}".
                    @else
                        Nenhum recurso cadastrado ainda.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Recurso</th>
                        <th>Tipo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recursos as $recurso)
                        <tr class="{{ !$recurso->ativo ? 'text-muted' : '' }}">
                            <td>
                                <i class="bi {{ $recurso->icone }} me-2"></i>
                                <strong>{{ $recurso->nome }}</strong>
                                @if (!$recurso->ativo)
                                    <span class="badge bg-secondary ms-1">Inativo</span>
                                @endif
                                @if ($recurso->subtitulo)
                                    <div class="small text-muted">{{ $recurso->subtitulo }}</div>
                                @endif
                            </td>
                            <td><span class="tag-chip">{{ $recurso->tipo_recurso }}</span></td>
                            <td>
                                <span class="status-badge status-badge-{{ $statusSlug[$recurso->status] ?? 'indisponivel' }}">
                                    {{ $recurso->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
