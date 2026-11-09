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
    <x-page-header title="Ocorrências" icon="bi-fire" action-route="ocorrencias.create" action-text="Registrar Ocorrência">
        <x-slot:actions>
            <button type="button" class="btn btn-outline-secondary btn-pill" data-bs-toggle="modal" data-bs-target="#modalGerenciarTiposOcorrencia">
                <i class="bi bi-tags"></i> Tipos
            </button>
            <button type="button" class="btn btn-outline-secondary btn-pill" data-bs-toggle="modal" data-bs-target="#modalGerenciarPrioridades">
                <i class="bi bi-flag"></i> Prioridades
            </button>
        </x-slot:actions>
    </x-page-header>

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
                    <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control form-control-sm" style="width: 150px;" title="Data inicial">
                    <input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control form-control-sm" style="width: 150px;" title="Data final">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por endereço ou bairro...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap"><i class="bi bi-search"></i></button>
                    @if ($busca || $status || $prioridadeId || $dataInicio || $dataFim)
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
                                @if ($busca || $status || $prioridadeId || $dataInicio || $dataFim)
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

    <div class="modal fade" id="modalGerenciarTiposOcorrencia" tabindex="-1" aria-labelledby="modalGerenciarTiposOcorrenciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGerenciarTiposOcorrenciaLabel"><i class="bi bi-tags"></i> Gerenciar Tipos de Ocorrência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('tipos-ocorrencia.store') }}" class="d-flex gap-2 mb-3">
                        @csrf
                        <input type="text" name="nome" placeholder="Nome do novo tipo" autofocus
                               class="form-control @error('nome', 'novo_tipo_ocorrencia') is-invalid @enderror">
                        <button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-plus-lg"></i> Adicionar</button>
                        @error('nome', 'novo_tipo_ocorrencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </form>

                    <div class="manage-list">
                        @forelse ($tiposComContagem as $tipo)
                            <div class="manage-list-item">
                                <form method="POST" action="{{ route('tipos-ocorrencia.update', $tipo) }}" class="manage-list-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="nome" value="{{ $tipo->nome }}" class="form-control form-control-sm" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap" title="Salvar novo nome">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <span class="badge {{ $tipo->ocorrencias_count > 0 ? 'bg-info' : 'bg-secondary' }}"
                                      title="Ocorrências cadastradas com este tipo">
                                    {{ $tipo->ocorrencias_count }}
                                </span>
                                @if ($tipo->ocorrencias_count > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                            title="Não é possível excluir: {{ $tipo->ocorrencias_count }} ocorrência(s) usam esse tipo">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('tipos-ocorrencia.destroy', $tipo) }}"
                                          data-confirm="Excluir o tipo '{{ $tipo->nome }}'?">
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

    @if ($errors->novo_tipo_ocorrencia->any() || session('reabrir_modal_tipos_ocorrencia'))
        <script>
            new bootstrap.Modal(document.getElementById('modalGerenciarTiposOcorrencia')).show();
        </script>
    @endif

    <div class="modal fade" id="modalGerenciarPrioridades" tabindex="-1" aria-labelledby="modalGerenciarPrioridadesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGerenciarPrioridadesLabel"><i class="bi bi-flag"></i> Gerenciar Prioridades</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('prioridades.store') }}" class="d-flex gap-2 mb-3">
                        @csrf
                        <input type="text" name="nome" placeholder="Nome da nova prioridade" autofocus
                               class="form-control @error('nome', 'nova_prioridade') is-invalid @enderror">
                        <button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-plus-lg"></i> Adicionar</button>
                        @error('nome', 'nova_prioridade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </form>

                    <div class="manage-list">
                        @forelse ($prioridadesComContagem as $prioridade)
                            <div class="manage-list-item">
                                <form method="POST" action="{{ route('prioridades.update', $prioridade) }}" class="manage-list-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="nome" value="{{ $prioridade->nome }}" class="form-control form-control-sm" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap" title="Salvar novo nome">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <span class="badge {{ $prioridade->ocorrencias_count > 0 ? 'bg-info' : 'bg-secondary' }}"
                                      title="Ocorrências cadastradas com esta prioridade">
                                    {{ $prioridade->ocorrencias_count }}
                                </span>
                                @if ($prioridade->ocorrencias_count > 0)
                                    <button type="button" class="btn btn-sm btn-outline-danger" disabled
                                            title="Não é possível excluir: {{ $prioridade->ocorrencias_count }} ocorrência(s) usam essa prioridade">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('prioridades.destroy', $prioridade) }}"
                                          data-confirm="Excluir a prioridade '{{ $prioridade->nome }}'?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">Nenhuma prioridade cadastrada.</p>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->nova_prioridade->any() || session('reabrir_modal_prioridades'))
        <script>
            new bootstrap.Modal(document.getElementById('modalGerenciarPrioridades')).show();
        </script>
    @endif
@endsection
