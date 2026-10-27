@extends('layouts.app')

@section('title', 'Viaturas')

@php
    $statusSlug = [
        'Disponível' => 'disponivel',
        'Em Atendimento' => 'uso',
        'Em Manutenção' => 'manutencao',
    ];
@endphp

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0"><i class="bi bi-truck"></i> Viaturas</h1>
        <button type="button" class="btn btn-primary btn-pill" data-bs-toggle="modal" data-bs-target="#modalNovaViatura">
            <i class="bi bi-plus-lg"></i> Nova Viatura
        </button>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="mb-0 text-muted">
            Mostrando {{ $viaturas->count() }} de {{ $viaturas->total() }} viaturas
        </p>
        <div class="filter-pill-group" role="group">
            <a href="{{ route('viaturas.index', ['busca' => $busca]) }}"
               class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }}">Todos</a>
            <a href="{{ route('viaturas.index', ['busca' => $busca, 'status' => 'ativos']) }}"
               class="btn btn-sm {{ $status === 'ativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Ativos</a>
            <a href="{{ route('viaturas.index', ['busca' => $busca, 'status' => 'inativos']) }}"
               class="btn btn-sm {{ $status === 'inativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Inativos</a>
        </div>
        <form method="GET" action="{{ route('viaturas.index') }}" class="search-inline" style="width: 300px; max-width: 100%;">
            <i class="bi bi-search"></i>
            <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Buscar por placa ou tipo...">
            <input type="hidden" name="status" value="{{ $status }}">
            @if ($busca)
                <a href="{{ route('viaturas.index', ['status' => $status]) }}" class="small d-block mt-1 text-muted">Limpar busca</a>
            @endif
        </form>
    </div>

    @if ($viaturas->isEmpty())
        <div class="equip-card">
            <div class="text-center text-muted py-5">
                <i class="bi bi-truck" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                <p class="mt-2 mb-0">
                    @if ($busca && $status)
                        Nenhuma viatura {{ $status === 'ativos' ? 'ativa' : 'inativa' }} encontrada para "{{ $busca }}".
                    @elseif ($busca)
                        Nenhuma viatura encontrada para "{{ $busca }}".
                    @elseif ($status)
                        Nenhuma viatura {{ $status === 'ativos' ? 'ativa' : 'inativa' }} encontrada.
                    @else
                        Nenhuma viatura cadastrada ainda.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($viaturas as $viatura)
                @php
                    $slug = !$viatura->ativo ? 'indisponivel' : ($statusSlug[$viatura->status] ?? 'indisponivel');
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="equip-card equip-card-accent-{{ $slug }}">
                        <div class="equip-card-top">
                            <div class="d-flex gap-2 min-w-0">
                                <div class="equip-card-icon"><i class="bi {{ $viatura->iconeTipo() }}"></i></div>
                                <div class="equip-card-heading">
                                    <p class="equip-card-name">{{ $viatura->identificacao }} — {{ $viatura->tipo }}</p>
                                    <span class="tag-chip-subtle">{{ $viatura->placa }}</span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button type="button" class="equip-card-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Mais ações">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('viaturas.edit', $viatura) }}">
                                            <i class="bi bi-pencil me-2"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('viaturas.toggle-ativo', $viatura) }}"
                                              @if ($viatura->ativo) data-confirm="Inativar a viatura '{{ $viatura->identificacao }}'? Ela deixará de aparecer como disponível." @endif>
                                            @csrf
                                            @method('PATCH')
                                            @if ($viatura->ativo)
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
                                </ul>
                            </div>
                        </div>

                        @if ($viatura->modelo)
                            <div class="equip-card-meta">
                                <span class="row-item row-item-strong"><i class="bi bi-car-front"></i> {{ $viatura->modelo }}</span>
                            </div>
                        @endif

                        @if ($viatura->ativo && $viatura->status === 'Em Atendimento' && $viatura->atendimentoAtual)
                            <div class="equip-card-context">
                                <i class="bi bi-broadcast"></i>
                                <span>
                                    {{ $viatura->atendimentoAtual->equipe->nome ?? 'Equipe não informada' }}
                                    @if ($viatura->atendimentoAtual->ocorrencia)
                                        — Ocorrência #{{ $viatura->atendimentoAtual->ocorrencia->id }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="equip-card-foot justify-content-end">
                            @if (!$viatura->ativo)
                                <span class="status-badge status-badge-indisponivel"><i class="bi bi-slash-circle"></i> Inativa</span>
                            @else
                                <form method="POST" action="{{ route('viaturas.status', $viatura) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                            class="status-pill-select status-pill-{{ $statusSlug[$viatura->status] ?? 'indisponivel' }}">
                                        @foreach ($statusOpcoes as $opcao)
                                            <option value="{{ $opcao }}" {{ $viatura->status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
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
        {{ $viaturas->links() }}
    </div>

    <div class="modal fade" id="modalNovaViatura" tabindex="-1" aria-labelledby="modalNovaViaturaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('viaturas.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNovaViaturaLabel"><i class="bi bi-truck"></i> Nova Viatura</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Placa</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                    <input type="text" name="placa" value="{{ old('placa') }}" placeholder="ABC1D23" maxlength="10" style="text-transform: uppercase;"
                                           class="form-control @error('placa', 'viatura') is-invalid @enderror" required autofocus>
                                    @error('placa', 'viatura')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Identificação</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="identificacao" value="{{ old('identificacao') }}" placeholder="Ex: Viatura 01"
                                           class="form-control @error('identificacao', 'viatura') is-invalid @enderror" required>
                                    @error('identificacao', 'viatura')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-signpost-split"></i></span>
                                <select name="tipo" class="form-select @error('tipo', 'viatura') is-invalid @enderror" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($tipoOpcoes as $opcao)
                                        <option value="{{ $opcao }}" {{ old('tipo') === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                    @endforeach
                                </select>
                                @error('tipo', 'viatura')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Modelo <span class="text-muted">(Opcional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-car-front"></i></span>
                                <input type="text" name="modelo" value="{{ old('modelo') }}" placeholder="Ex: Mercedes Sprinter"
                                       class="form-control @error('modelo', 'viatura') is-invalid @enderror">
                                @error('modelo', 'viatura')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                        <button type="submit" class="btn btn-primary btn-pill"><i class="bi bi-check-lg"></i> Cadastrar</button>
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
