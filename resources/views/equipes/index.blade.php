@extends('layouts.app')

@section('title', 'Equipes')

@php
    $statusSlug = [
        'Disponível' => 'disponivel',
        'Em Serviço' => 'uso',
        'Indisponível' => 'indisponivel',
    ];
@endphp

@section('content')
    <x-page-header title="Equipes" icon="bi-people-fill" action-route="equipes.create" action-text="Nova Equipe" />

    <x-filter-bar
        route-name="equipes.index"
        :search="$busca"
        :status="$status"
        search-placeholder="Buscar por nome..."
        label="Mostrando {{ $equipes->count() }} de {{ $equipes->total() }} equipes"
    />

    @if ($equipes->isEmpty())
        <div class="equip-card">
            <div class="text-center text-muted py-5">
                <i class="bi bi-people" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                <p class="mt-2 mb-0">
                    @if ($busca && $status)
                        Nenhuma equipe {{ $status === 'ativos' ? 'ativa' : 'inativa' }} encontrada para "{{ $busca }}".
                    @elseif ($busca)
                        Nenhuma equipe encontrada para "{{ $busca }}".
                    @elseif ($status)
                        Nenhuma equipe {{ $status === 'ativos' ? 'ativa' : 'inativa' }} encontrada.
                    @else
                        Nenhuma equipe cadastrada ainda.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($equipes as $equipe)
                <div class="col-md-6 col-lg-4">
                    <x-resource-card
                        :title="$equipe->nome"
                        :subtitle="$equipe->tipoEquipe->nome"
                        :border-color="!$equipe->ativo ? 'indisponivel' : ($statusSlug[$equipe->status] ?? 'indisponivel')"
                    >
                        <x-slot:actions>
                            <li>
                                <a class="dropdown-item" href="{{ route('equipes.edit', $equipe) }}">
                                    <i class="bi bi-pencil me-2"></i> Editar
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('equipes.toggle-ativo', $equipe) }}"
                                      @if ($equipe->ativo) data-confirm="Inativar '{{ $equipe->nome }}'? Ela deixará de aparecer como disponível para despacho." @endif>
                                    @csrf
                                    @method('PATCH')
                                    @if ($equipe->ativo)
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-slash-circle me-2"></i> Inativar
                                        </button>
                                    @else
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-check-circle me-2"></i> Reativar
                                        </button>
                                    @endif
                                </form>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('equipes.destroy', $equipe) }}"
                                      data-confirm="Excluir '{{ $equipe->nome }}'? Essa ação não pode ser desfeita.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-trash me-2"></i> Excluir
                                    </button>
                                </form>
                            </li>
                        </x-slot:actions>

                        <div class="equip-card-meta">
                            <span class="row-item"><i class="bi bi-person-check"></i> {{ $equipe->voluntarios->count() }} membro(s)</span>
                        </div>

                        @if (!$equipe->ativo)
                            <x-slot:footer>
                                <x-status-badge text="Inativo" color="indisponivel" icon="bi-slash-circle" />
                            </x-slot:footer>
                        @else
                            <x-slot:footer>
                                <form method="POST" action="{{ route('equipes.status', $equipe) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="status-pill-select status-pill-{{ $statusSlug[$equipe->status] ?? 'indisponivel' }}">
                                        @foreach ($statusOpcoes as $opcao)
                                            <option value="{{ $opcao }}" {{ $equipe->status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </x-slot:footer>
                        @endif
                    </x-resource-card>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-3">
        {{ $equipes->links() }}
    </div>
@endsection
