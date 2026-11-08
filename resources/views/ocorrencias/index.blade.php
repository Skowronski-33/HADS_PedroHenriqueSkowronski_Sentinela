@extends('layouts.app')

@section('title', 'Ocorrências')

@php
    $corStatus = [
        'Aberta' => 'bg-warning',
        'Triada' => 'bg-primary',
        'Em Atendimento' => 'bg-info',
        'Encerrada' => 'bg-success',
        'Descartada' => 'bg-secondary',
    ];
    $corPrioridade = [
        'Baixa' => 'bg-secondary',
        'Média' => 'bg-info',
        'Alta' => 'bg-warning',
        'Crítica' => 'bg-danger',
    ];
@endphp

@section('content')
    <x-page-header title="Ocorrências" icon="bi-fire" action-route="ocorrencias.create" action-text="Registrar Ocorrência" />

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('ocorrencias.index') }}" class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <p class="mb-0 text-muted text-nowrap">
                    Mostrando {{ $ocorrencias->count() }} de {{ $ocorrencias->total() }} ocorrências
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <select name="status" class="form-select form-select-sm" style="width: 170px;" onchange="this.form.submit()">
                        <option value="">Todos os status</option>
                        @foreach ($statusOpcoes as $opcao)
                            <option value="{{ $opcao }}" {{ $status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                        @endforeach
                    </select>
                    <select name="prioridade_id" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                        <option value="">Todas as prioridades</option>
                        @foreach ($prioridades as $prioridade)
                            <option value="{{ $prioridade->id }}" {{ (int) $prioridadeId === $prioridade->id ? 'selected' : '' }}>{{ $prioridade->nome }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por endereço ou bairro...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-search"></i></button>
                    @if ($busca || $status || $prioridadeId)
                        <a href="{{ route('ocorrencias.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Prioridade</th>
                        <th>Tipo</th>
                        <th>Endereço</th>
                        <th>Data/Hora</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ocorrencias as $ocorrencia)
                        <tr>
                            <td>
                                @if ($ocorrencia->prioridade)
                                    <x-status-badge :text="$ocorrencia->prioridade->nome" :color="$corPrioridade[$ocorrencia->prioridade->nome] ?? 'bg-secondary'" />
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $ocorrencia->tipoOcorrencia->nome }}</td>
                            <td>{{ $ocorrencia->enderecoCompleto() }}</td>
                            <td>{{ $ocorrencia->data_hora->format('d/m/Y H:i') }}</td>
                            <td><x-status-badge :text="$ocorrencia->status" :color="$corStatus[$ocorrencia->status] ?? 'bg-secondary'" /></td>
                            <td class="text-end">
                                <a href="{{ route('ocorrencias.show', $ocorrencia) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                @if ($busca || $status || $prioridadeId)
                                    Nenhuma ocorrência encontrada para os filtros aplicados.
                                @else
                                    Nenhuma ocorrência registrada ainda.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ocorrencias->hasPages())
            <div class="card-body border-top">
                {{ $ocorrencias->links() }}
            </div>
        @endif
    </div>
@endsection
