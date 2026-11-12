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
                    >
                        <x-slot:headerBadge>
                            <x-status-badge :text="$statusText" :color="$statusColor" />
                        </x-slot:headerBadge>

                        <x-slot:actions>
                            <li>
                                <button type="button" class="dropdown-item" data-href="{{ route('abrigos.edit', $abrigo) }}">
                                    <i class="bi bi-pencil me-2"></i> Editar Informações
                                </button>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('abrigos.toggle-ativo', $abrigo) }}"
                                      @if ($abrigo->ativo) data-confirm="Inativar o abrigo '{{ $abrigo->nome }}'?" @endif>
                                    @csrf
                                    @method('PATCH')
                                    @if ($abrigo->ativo)
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-slash-circle me-2"></i> Inativar Abrigo
                                        </button>
                                    @else
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-check-circle me-2"></i> Reativar Abrigo
                                        </button>
                                    @endif
                                </form>
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

                        <x-slot:footer>
                            <a data-href="{{ route('abrigos.show', $abrigo) }}" class="btn btn-primary w-100 stretched-link" role="link" tabindex="0">
                                <i class="bi bi-gear-fill"></i> Gerenciar Abrigo
                            </a>
                        </x-slot:footer>
                    </x-resource-card>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-3">
        {{ $abrigos->links() }}
    </div>
@endsection
