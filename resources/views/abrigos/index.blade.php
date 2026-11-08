@extends('layouts.app')

@section('title', 'Abrigos')

@section('content')
    <x-page-header title="Abrigos" icon="bi-houses-fill" action-route="abrigos.create" action-text="Novo Abrigo" />

    @if ($abrigos->isEmpty())
        <div class="equip-card">
            <div class="text-center text-muted py-5">
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

                    $borderColor = ! $abrigo->ativo ? 'indisponivel' : ($abrigo->status === 'Lotado' ? 'manutencao' : 'disponivel');
                    $statusText = ! $abrigo->ativo ? 'Inativo' : $abrigo->status;
                    $statusColor = ! $abrigo->ativo ? 'indisponivel' : ($abrigo->status === 'Lotado' ? 'manutencao' : 'disponivel');
                @endphp
                <div class="col-md-6 col-lg-4">
                    <x-resource-card
                        :title="$abrigo->nome"
                        icon="bi-house-door-fill"
                        :border-color="$borderColor"
                        :status-text="$statusText"
                        :status-color="$statusColor"
                    >
                        <x-slot:actions>
                            <li>
                                <a class="dropdown-item" href="{{ route('abrigos.show', $abrigo) }}">
                                    <i class="bi bi-eye me-2"></i> Gerenciar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('abrigos.edit', $abrigo) }}">
                                    <i class="bi bi-pencil me-2"></i> Editar
                                </a>
                            </li>
                        </x-slot:actions>

                        <div class="equip-card-meta">
                            <span class="row-item"><i class="bi bi-geo-alt"></i> {{ $abrigo->enderecoCompleto() }}</span>
                            <span class="row-item"><i class="bi bi-person-badge"></i> {{ $abrigo->responsavel ?: 'Sem responsável definido' }}</span>
                        </div>

                        <x-slot:extraContent>
                            <div class="equip-card-meta">
                                <div class="entity-mini-stat mb-1">
                                    <span><i class="bi bi-people"></i> Ocupação</span>
                                    <strong>{{ $abrigo->ocupacao_atual }} / {{ $abrigo->capacidade }}</strong>
                                </div>
                                <div class="progress" style="height: .5rem;">
                                    <div class="progress-bar {{ $corBarra }}" style="width: {{ $percentual }}%"></div>
                                </div>
                            </div>
                        </x-slot:extraContent>
                    </x-resource-card>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-3">
        {{ $abrigos->links() }}
    </div>
@endsection
