@extends('layouts.app')

@section('title', 'Abrigos')

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0"><i class="bi bi-houses-fill"></i> Abrigos</h1>
        <a href="{{ route('abrigos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Abrigo
        </a>
    </div>

    @if ($abrigos->isEmpty())
        <div class="entity-card">
            <div class="entity-card-body text-center text-muted py-5">
                <i class="bi bi-houses" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                <p class="mt-2 mb-0">Nenhum abrigo cadastrado ainda.</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($abrigos as $abrigo)
                @php
                    $percentual = $abrigo->capacidade > 0 ? min(100, round(($abrigo->ocupacao_atual / $abrigo->capacidade) * 100)) : 0;
                    $corBarra = $abrigo->ocupacao_atual >= $abrigo->capacidade ? 'bg-danger' : ($percentual >= 80 ? 'bg-warning' : 'bg-success');
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="entity-card">
                        <div class="entity-card-header">
                            <div class="entity-card-icon"><i class="bi bi-house-door-fill"></i></div>
                            <div class="flex-grow-1 overflow-hidden min-w-0">
                                <p class="entity-card-title">{{ $abrigo->nome }}</p>
                                <p class="entity-card-subtitle mb-0">
                                    <i class="bi bi-geo-alt"></i> {{ $abrigo->enderecoCompleto() }}
                                </p>
                            </div>
                        </div>
                        <div class="entity-card-body">
                            <div>
                                <div class="entity-mini-stat mb-1">
                                    <span><i class="bi bi-people"></i> Ocupação</span>
                                    <strong>{{ $abrigo->ocupacao_atual }} / {{ $abrigo->capacidade }}</strong>
                                </div>
                                <div class="progress" style="height: .5rem;">
                                    <div class="progress-bar {{ $corBarra }}" style="width: {{ $percentual }}%"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="bi bi-person-badge"></i> {{ $abrigo->responsavel ?: 'Sem responsável definido' }}</span>
                                @if (!$abrigo->ativo)
                                    <span class="badge bg-secondary">Inativo</span>
                                @elseif ($abrigo->status === 'Lotado')
                                    <span class="badge bg-warning text-dark">Lotado</span>
                                @else
                                    <span class="badge bg-success">{{ $abrigo->status }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="entity-card-footer">
                            <a href="{{ route('abrigos.show', $abrigo) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Gerenciar
                            </a>
                            <a href="{{ route('abrigos.edit', $abrigo) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-3">
        {{ $abrigos->links() }}
    </div>
@endsection
