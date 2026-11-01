@extends('layouts.app')

@section('title', $ocorrencia->tipoOcorrencia->nome)

@php
    $corStatus = [
        'Aberta' => 'bg-warning text-dark',
        'Em Atendimento' => 'bg-info',
        'Encerrada' => 'bg-success',
        'Cancelada' => 'bg-secondary',
    ];
    $corPrioridade = [
        'Baixa' => 'bg-secondary',
        'Média' => 'bg-info',
        'Alta' => 'bg-warning text-dark',
        'Crítica' => 'bg-danger',
    ];
    $corStatusAtendimento = [
        'Em Deslocamento' => 'bg-warning text-dark',
        'No Local' => 'bg-info',
        'Concluído' => 'bg-success',
    ];
    $podeDespachar = ! in_array($ocorrencia->status, ['Encerrada', 'Cancelada']);
@endphp

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="mb-1">
                <i class="bi bi-fire"></i> {{ $ocorrencia->tipoOcorrencia->nome }}
                <span class="badge {{ $corStatus[$ocorrencia->status] ?? 'bg-secondary' }}">{{ $ocorrencia->status }}</span>
                @if ($ocorrencia->prioridade)
                    <span class="badge {{ $corPrioridade[$ocorrencia->prioridade->nome] ?? 'bg-secondary' }}">{{ $ocorrencia->prioridade->nome }}</span>
                @endif
            </h1>
            <p class="text-muted mb-0"><i class="bi bi-geo-alt"></i> {{ $ocorrencia->enderecoCompleto() }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('ocorrencias.edit', $ocorrencia) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @if (in_array($ocorrencia->status, ['Aberta', 'Em Atendimento']))
                <form method="POST" action="{{ route('ocorrencias.encerrar', $ocorrencia) }}"
                      data-confirm="Encerrar esta ocorrência?">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-check-circle"></i> Encerrar
                    </button>
                </form>
            @endif
            @if ($ocorrencia->status === 'Aberta' && $ocorrencia->atendimentos->isEmpty())
                <form method="POST" action="{{ route('ocorrencias.cancelar', $ocorrencia) }}"
                      data-confirm="Cancelar esta ocorrência? Essa ação não pode ser desfeita.">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                </form>
            @endif
            <a href="{{ route('ocorrencias.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-info-circle"></i> Informações</div>
        <div class="card-body">
            <p class="mb-2">{{ $ocorrencia->descricao }}</p>
            @if ($ocorrencia->ponto_referencia)
                <p class="mb-2"><i class="bi bi-signpost text-muted"></i> <strong>Ponto de referência:</strong> {{ $ocorrencia->ponto_referencia }}</p>
            @endif
            <p class="mb-2"><i class="bi bi-person text-muted"></i> <strong>Registrado por:</strong> {{ $ocorrencia->usuario->name }}</p>
            <p class="mb-2"><i class="bi bi-clock text-muted"></i> <strong>Data/hora:</strong> {{ $ocorrencia->data_hora->format('d/m/Y H:i') }}</p>
            @if ($ocorrencia->observacoes)
                <p class="mb-0"><i class="bi bi-journal-text text-muted"></i> <strong>Observações:</strong> {{ $ocorrencia->observacoes }}</p>
            @endif
        </div>
    </div>

    @if ($podeDespachar)
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-truck"></i> Despachar Atendimento</div>
            <div class="card-body">
                <form method="POST" action="{{ route('ocorrencias.atendimentos.store', $ocorrencia) }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="equipe_id" class="form-label">Equipe</label>
                            <select name="equipe_id" id="equipe_id" class="form-select @error('equipe_id') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                @foreach ($equipesDisponiveis as $equipe)
                                    <option value="{{ $equipe->id }}">{{ $equipe->nome }} ({{ $equipe->tipoEquipe->nome }})</option>
                                @endforeach
                            </select>
                            @if ($equipesDisponiveis->isEmpty())
                                <div class="form-text text-warning">Nenhuma equipe disponível no momento.</div>
                            @endif
                            @error('equipe_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="viatura_id" class="form-label">Viatura <span class="text-muted">(Opcional)</span></label>
                            <select name="viatura_id" id="viatura_id" class="form-select @error('viatura_id') is-invalid @enderror">
                                <option value="">Nenhuma</option>
                                @foreach ($viaturasDisponiveis as $viatura)
                                    <option value="{{ $viatura->id }}">{{ $viatura->placa }} — {{ $viatura->identificacao }}</option>
                                @endforeach
                            </select>
                            @error('viatura_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if ($equipamentosDisponiveis->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label">Equipamentos <span class="text-muted">(Opcional)</span></label>
                            <div class="row">
                                @foreach ($equipamentosDisponiveis as $equipamento)
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" name="equipamentos[]" value="{{ $equipamento->id }}"
                                                   id="equipamento_{{ $equipamento->id }}" class="form-check-input mt-0"
                                                   onchange="document.getElementById('qtd_{{ $equipamento->id }}').disabled = !this.checked">
                                            <label for="equipamento_{{ $equipamento->id }}" class="form-check-label flex-grow-1">
                                                {{ $equipamento->nome }}
                                            </label>
                                            <input type="number" name="quantidades[{{ $equipamento->id }}]" id="qtd_{{ $equipamento->id }}"
                                                   value="1" min="1" class="form-control form-control-sm" style="width: 70px;" disabled>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="observacoes_atendimento" class="form-label">Observações <span class="text-muted">(Opcional)</span></label>
                        <textarea name="observacoes" id="observacoes_atendimento" rows="2"
                                  class="form-control auto-resize @error('observacoes') is-invalid @enderror"
                                  style="resize: none; overflow: hidden;">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" {{ $equipesDisponiveis->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-send"></i> Despachar
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><i class="bi bi-list-check"></i> Atendimentos</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Equipe</th>
                        <th>Viatura</th>
                        <th>Equipamentos</th>
                        <th>Saída</th>
                        <th>Retorno</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ocorrencia->atendimentos as $atendimento)
                        <tr>
                            <td>{{ $atendimento->equipe->nome ?? '-' }}</td>
                            <td>{{ $atendimento->viatura->placa ?? '-' }}</td>
                            <td>
                                @forelse ($atendimento->equipamentos as $equipamento)
                                    <span class="tag-chip-subtle">{{ $equipamento->nome }} ({{ $equipamento->pivot->quantidade_utilizada }})</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>{{ $atendimento->data_hora_saida?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $atendimento->data_hora_retorno?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>
                                @if ($atendimento->status === 'Concluído')
                                    <span class="badge {{ $corStatusAtendimento[$atendimento->status] ?? 'bg-secondary' }}">{{ $atendimento->status }}</span>
                                @else
                                    <form method="POST" action="{{ route('ocorrencias.atendimentos.status', [$ocorrencia, $atendimento]) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                                            @foreach ($atendimentoStatusOpcoes as $opcao)
                                                <option value="{{ $opcao }}" {{ $atendimento->status === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum atendimento despachado ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
