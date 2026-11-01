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
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Equipes</h1>
        <a href="{{ route('equipes.create') }}" class="btn btn-primary btn-pill">
            <i class="bi bi-plus-lg"></i> Nova Equipe
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="mb-0 text-muted">
            Mostrando {{ $equipes->count() }} de {{ $equipes->total() }} equipes
        </p>
        <div class="btn-group" role="group">
            <a href="{{ route('equipes.index', ['busca' => $busca]) }}"
               class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }}">Todos</a>
            <a href="{{ route('equipes.index', ['busca' => $busca, 'status' => 'ativos']) }}"
               class="btn btn-sm {{ $status === 'ativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Ativos</a>
            <a href="{{ route('equipes.index', ['busca' => $busca, 'status' => 'inativos']) }}"
               class="btn btn-sm {{ $status === 'inativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Inativos</a>
        </div>
        <form method="GET" action="{{ route('equipes.index') }}" class="search-inline" style="width: 300px; max-width: 100%;">
            <i class="bi bi-search"></i>
            <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Buscar por nome...">
            <input type="hidden" name="status" value="{{ $status }}">
            @if ($busca)
                <a href="{{ route('equipes.index', ['status' => $status]) }}" class="small d-block mt-1 text-muted">Limpar busca</a>
            @endif
        </form>
    </div>

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
                    <div class="equip-card">
                        <div class="equip-card-top">
                            <div class="d-flex gap-2 min-w-0">
                                <div class="equip-card-icon"><i class="bi bi-people-fill"></i></div>
                                <div class="equip-card-heading">
                                    <p class="equip-card-name">{{ $equipe->nome }}</p>
                                    <span class="tag-chip-subtle">{{ $equipe->tipoEquipe->nome }}</span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button type="button" class="equip-card-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Mais ações">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
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
                                </ul>
                            </div>
                        </div>

                        <div class="equip-card-meta">
                            <span class="row-item"><i class="bi bi-person-check"></i> {{ $equipe->voluntarios->count() }} membro(s)</span>
                        </div>

                        <div class="equip-card-foot justify-content-end">
                            @if (!$equipe->ativo)
                                <span class="status-badge status-badge-indisponivel"><i class="bi bi-slash-circle"></i> Inativo</span>
                            @else
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
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-3">
        {{ $equipes->links() }}
    </div>
@endsection
