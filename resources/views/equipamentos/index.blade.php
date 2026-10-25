@extends('layouts.app')

@section('title', 'Equipamentos')

@php
    $statusSlug = [
        'Disponível' => 'disponivel',
        'Em Uso' => 'uso',
        'Em Manutenção' => 'manutencao',
        'Indisponível' => 'indisponivel',
    ];
@endphp

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Equipamentos</h1>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-pill" data-bs-toggle="modal" data-bs-target="#modalGerenciarTipos">
                <i class="bi bi-gear"></i> Gerenciar Tipos
            </button>
            <button type="button" class="btn btn-primary btn-pill" data-bs-toggle="modal" data-bs-target="#modalNovoEquipamento">
                <i class="bi bi-plus-lg"></i> Novo Equipamento
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="mb-0 text-muted">
            Mostrando {{ $equipamentos->count() }} de {{ $equipamentos->total() }} equipamentos
        </p>
        <form method="GET" action="{{ route('equipamentos.index') }}" class="search-inline" style="width: 300px; max-width: 100%;">
            <i class="bi bi-search"></i>
            <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Buscar por nome...">
            @if ($busca)
                <a href="{{ route('equipamentos.index') }}" class="small d-block mt-1 text-muted">Limpar busca</a>
            @endif
        </form>
    </div>

    @if ($equipamentos->isEmpty())
        <div class="equip-card">
            <div class="text-center text-muted py-5">
                <i class="bi bi-tools" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                <p class="mt-2 mb-0">
                    @if ($busca)
                        Nenhum equipamento encontrado para "{{ $busca }}".
                    @else
                        Nenhum equipamento cadastrado ainda.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($equipamentos as $equipamento)
                <div class="col-md-6 col-lg-4">
                    <div class="equip-card">
                        <div class="equip-card-top">
                            <div class="d-flex gap-2 min-w-0">
                                <div class="equip-card-icon"><i class="bi {{ $equipamento->iconeTipo() }}"></i></div>
                                <div class="equip-card-heading">
                                    <p class="equip-card-name">{{ $equipamento->nome }}</p>
                                    <span class="tag-chip-subtle">{{ $equipamento->tipoEquipamento->nome }}</span>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button type="button" class="equip-card-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Mais ações">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('equipamentos.edit', $equipamento) }}">
                                            <i class="bi bi-pencil me-2"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('equipamentos.destroy', $equipamento) }}"
                                              onsubmit="return confirm('Excluir \'{{ $equipamento->nome }}\'? Essa ação não pode ser desfeita.')">
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

                        @if ($equipamento->patrimonio || $equipamento->localizacao || $equipamento->data_ultima_manutencao)
                            <div class="equip-card-meta">
                                @if ($equipamento->patrimonio)
                                    <span class="row-item"><i class="bi bi-upc-scan"></i> Patrimônio {{ $equipamento->patrimonio }}</span>
                                @endif
                                @if ($equipamento->localizacao)
                                    <span class="row-item"><i class="bi bi-geo-alt"></i> {{ $equipamento->localizacao }}</span>
                                @endif
                                @if ($equipamento->data_ultima_manutencao)
                                    <span class="row-item"><i class="bi bi-wrench"></i> Manutenção em {{ $equipamento->data_ultima_manutencao->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="equip-card-foot">
                            @if (!$equipamento->ativo)
                                <span class="equip-card-inactive"><i class="bi bi-slash-circle"></i> Inativo</span>
                            @else
                                <span></span>
                            @endif
                            <form method="POST" action="{{ route('equipamentos.status', $equipamento) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="status-pill-select status-pill-{{ $statusSlug[$equipamento->status] ?? 'indisponivel' }}">
                                    @foreach ($statusOpcoes as $opcao)
                                        <option value="{{ $opcao }}" {{ $equipamento->status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-3">
        {{ $equipamentos->links() }}
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
                            <div class="combobox" data-options="{{ $tipos->pluck('nome')->toJson() }}">
                                <input type="text" name="tipo_equipamento" value="{{ old('tipo_equipamento') }}"
                                       placeholder="Escolha um existente ou digite um novo" autocomplete="off"
                                       class="combobox-input form-control @error('tipo_equipamento', 'equipamento') is-invalid @enderror" required>
                                <ul class="combobox-list" hidden></ul>
                            </div>
                            <div class="form-text">Digitando um tipo novo, ele é cadastrado automaticamente.</div>
                            @error('tipo_equipamento', 'equipamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patrimônio/Série <span class="text-muted">(Opcional)</span></label>
                                <input type="text" name="patrimonio" value="{{ old('patrimonio') }}"
                                       class="form-control @error('patrimonio', 'equipamento') is-invalid @enderror">
                                @error('patrimonio', 'equipamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Localização <span class="text-muted">(Opcional)</span></label>
                                <input type="text" name="localizacao" value="{{ old('localizacao') }}" placeholder="Ex: Depósito Central"
                                       class="form-control @error('localizacao', 'equipamento') is-invalid @enderror">
                                @error('localizacao', 'equipamento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Última manutenção <span class="text-muted">(Opcional)</span></label>
                            <input type="date" name="data_ultima_manutencao" value="{{ old('data_ultima_manutencao') }}"
                                   class="form-control @error('data_ultima_manutencao', 'equipamento') is-invalid @enderror">
                            @error('data_ultima_manutencao', 'equipamento')
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

    <div class="modal fade" id="modalGerenciarTipos" tabindex="-1" aria-labelledby="modalGerenciarTiposLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGerenciarTiposLabel"><i class="bi bi-tags"></i> Gerenciar Tipos de Equipamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('tipos-equipamento.store') }}" class="d-flex gap-2 mb-3">
                        @csrf
                        <input type="text" name="nome" placeholder="Nome do novo tipo" autofocus
                               class="form-control @error('nome', 'novo_tipo') is-invalid @enderror">
                        <button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-plus-lg"></i> Adicionar</button>
                        @error('nome', 'novo_tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </form>

                    <div class="manage-list">
                        @forelse ($tiposComContagem as $tipo)
                            <div class="manage-list-item">
                                <form method="POST" action="{{ route('tipos-equipamento.update', $tipo) }}" class="manage-list-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="nome" value="{{ $tipo->nome }}" class="form-control form-control-sm" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap" title="Salvar novo nome">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <span class="badge {{ $tipo->equipamentos_count > 0 ? 'bg-info' : 'bg-secondary' }}"
                                      title="Equipamentos cadastrados com este tipo">
                                    {{ $tipo->equipamentos_count }}
                                </span>
                                @if ($tipo->equipamentos_count > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                            title="Não é possível excluir: {{ $tipo->equipamentos_count }} equipamento(s) usam esse tipo">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('tipos-equipamento.destroy', $tipo) }}"
                                          onsubmit="return confirm('Excluir o tipo \'{{ $tipo->nome }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">Nenhum tipo cadastrado.</p>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->novo_tipo->any() || session('reabrir_modal_tipos'))
        <script>
            new bootstrap.Modal(document.getElementById('modalGerenciarTipos')).show();
        </script>
    @endif
@endsection
