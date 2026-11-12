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
    <x-page-header title="Viaturas" icon="bi-truck" modal-target="modalNovaViatura" action-text="Nova Viatura" />

    <x-filter-bar
        route-name="viaturas.index"
        :search="$busca"
        :status="$status"
        search-placeholder="Buscar por placa ou tipo..."
        label="Mostrando {{ $viaturas->count() }} de {{ $viaturas->total() }} viaturas"
    />

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
                <div class="col-md-6 col-lg-4">
                    <x-resource-card
                        class="viatura-card"
                        :title="$viatura->identificacao"
                        :subtitle="$viatura->tipo"
                        :subtitle-color="$viatura->corTipo()"
                        :border-color="!$viatura->ativo ? 'indisponivel' : ($statusSlug[$viatura->status] ?? 'indisponivel')"
                    >
                        <x-slot:actions>
                            <li>
                                <button type="button" class="dropdown-item" data-href="{{ route('viaturas.edit', $viatura) }}">
                                    <i class="bi bi-pencil me-2"></i> Editar
                                </button>
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
                        </x-slot:actions>

                        <div class="equip-card-meta">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="tag-chip-subtle">{{ $viatura->placa }}</span>
                                @if ($viatura->modelo)
                                    <span class="row-item"><i class="bi bi-truck"></i> {{ $viatura->modelo }}</span>
                                @endif
                            </div>
                        </div>

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

                        @if (!$viatura->ativo)
                            <x-slot:footer>
                                <x-status-badge text="Inativa" color="indisponivel" icon="bi-slash-circle" />
                            </x-slot:footer>
                        @else
                            <x-slot:footer>
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
                            </x-slot:footer>
                        @endif
                    </x-resource-card>
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
