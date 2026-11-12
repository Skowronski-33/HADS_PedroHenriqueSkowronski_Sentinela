@extends('layouts.app')

@section('title', $ocorrencia->tipoOcorrencia->nome)

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
    $corStatusAtendimento = [
        'Em Deslocamento' => 'bg-warning',
        'No Local' => 'bg-info',
        'Concluído' => 'bg-success',
    ];
    $podeDespachar = in_array($ocorrencia->status, ['Triada', 'Em Atendimento']) && $ocorrencia->prioridade_id;
    $podeTriar = in_array($ocorrencia->status, ['Aberta', 'Triada', 'Em Atendimento']);
    $ehCritica = $ocorrencia->prioridade?->nome === 'Crítica';
    $aguardandoEquipe = $podeDespachar && $equipesDisponiveis->isEmpty();
@endphp

@section('content')
    <x-page-header icon="bi-fire">
        <x-slot:title>
            {{ $ocorrencia->tipoOcorrencia->nome }}
            @if ($ehCritica)
                <i class="bi bi-exclamation-triangle-fill text-danger" title="Prioridade crítica — urgência máxima"></i>
            @endif
            <x-status-badge :text="$ocorrencia->status" :color="$corStatus[$ocorrencia->status] ?? 'bg-secondary'" />
            @if ($ocorrencia->prioridade)
                <x-status-badge :text="$ocorrencia->prioridade->nome" :color="$corPrioridade[$ocorrencia->prioridade->nome] ?? 'bg-secondary'" />
            @endif
            @if ($aguardandoEquipe)
                <x-status-badge text="Aguardando Equipe" color="bg-warning" icon="bi-hourglass-split" />
            @endif
        </x-slot:title>
        <x-slot:subtitle><i class="bi bi-geo-alt"></i> {{ $ocorrencia->enderecoCompleto() }}</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('ocorrencias.edit', $ocorrencia) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            @if ($podeTriar)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTriagem">
                    <i class="bi bi-clipboard-check"></i> {{ $ocorrencia->status === 'Aberta' ? 'Triar' : 'Revisar Triagem' }}
                </button>
            @endif
            @if (in_array($ocorrencia->status, ['Triada', 'Em Atendimento']))
                <form method="POST" action="{{ route('ocorrencias.encerrar', $ocorrencia) }}"
                      data-confirm="Encerrar esta ocorrência?">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-check-circle"></i> Encerrar
                    </button>
                </form>
            @endif
            @if (in_array($ocorrencia->status, ['Aberta', 'Triada']) && $ocorrencia->atendimentos->isEmpty())
                <form method="POST" action="{{ route('ocorrencias.descartar', $ocorrencia) }}"
                      data-confirm="Descartar esta ocorrência como improcedente? Essa ação não pode ser desfeita.">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle"></i> Descartar
                    </button>
                </form>
            @endif
            <a href="{{ route('ocorrencias.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-4 {{ $ehCritica ? 'border-top border-danger border-4' : '' }}">
        <div class="card-header"><i class="bi bi-info-circle"></i> Informações</div>
        <div class="card-body">
            <p class="mb-2">{{ $ocorrencia->descricao }}</p>
            @if ($ocorrencia->ponto_referencia)
                <p class="mb-2"><i class="bi bi-signpost text-muted"></i> <strong>Ponto de referência:</strong> {{ $ocorrencia->ponto_referencia }}</p>
            @endif
            <p class="mb-2"><i class="bi bi-person text-muted"></i> <strong>Registrado por:</strong> {{ $ocorrencia->usuario->name }}</p>
            <p class="mb-2"><i class="bi bi-clock text-muted"></i> <strong>Data/hora:</strong> {{ $ocorrencia->data_hora->format('d/m/Y H:i') }}</p>
            @if ($ocorrencia->observacoes)
                <p class="mb-2"><i class="bi bi-journal-text text-muted"></i> <strong>Observações:</strong> {{ $ocorrencia->observacoes }}</p>
            @endif
            @if ($ocorrencia->observacoes_triagem)
                <p class="mb-0"><i class="bi bi-clipboard-check text-muted"></i> <strong>Observações da triagem:</strong> {{ $ocorrencia->observacoes_triagem }}</p>
            @endif
        </div>
    </div>

    @if ($ocorrencia->status === 'Aberta')
        <div class="alert alert-warning d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTriagem" role="button" style="cursor: pointer;">
            <i class="bi bi-exclamation-triangle"></i>
            Esta ocorrência ainda não foi triada. Clique aqui para realizar a triagem e liberar o despacho.
        </div>
    @endif

    @if ($podeDespachar)
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-truck"></i> Despachar Atendimento</div>
            <div class="card-body">
                @if ($aguardandoEquipe)
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="bi bi-hourglass-split"></i>
                        Nenhuma equipe disponível no momento. A ocorrência está triada e pronta para despacho assim que uma equipe for liberada.
                    </div>
                @endif
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
                                    <x-status-badge :text="$atendimento->status" :color="$corStatusAtendimento[$atendimento->status] ?? 'bg-secondary'" />
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

    <div class="modal fade" id="modalTriagem" tabindex="-1" aria-labelledby="modalTriagemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('ocorrencias.triar', $ocorrencia) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTriagemLabel"><i class="bi bi-clipboard-check"></i> Triagem de Ocorrência</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tipo_ocorrencia_id" class="form-label">Tipo de Ocorrência</label>
                            <select name="tipo_ocorrencia_id" id="tipo_ocorrencia_id"
                                    class="form-select @error('tipo_ocorrencia_id', 'triagem') is-invalid @enderror" required>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id }}" {{ $ocorrencia->tipo_ocorrencia_id === $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_ocorrencia_id', 'triagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="prioridade_id" class="form-label">Prioridade</label>
                            <select name="prioridade_id" id="prioridade_id"
                                    class="form-select @error('prioridade_id', 'triagem') is-invalid @enderror" required>
                                <option value="" disabled {{ $ocorrencia->prioridade_id ? '' : 'selected' }}>Selecione...</option>
                                @foreach ($prioridades as $prioridade)
                                    @php
                                        $corTexto = match ($prioridade->nome) {
                                            'Baixa' => 'var(--bs-secondary)',
                                            'Média' => 'var(--bs-info)',
                                            'Alta' => 'var(--bs-warning)',
                                            'Crítica' => 'var(--bs-danger)',
                                            default => 'inherit',
                                        };
                                    @endphp
                                    <option value="{{ $prioridade->id }}" style="color: {{ $corTexto }};"
                                            {{ $ocorrencia->prioridade_id === $prioridade->id ? 'selected' : '' }}>
                                        {{ $prioridade->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prioridade_id', 'triagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Define a ordem de atendimento e libera o despacho de recursos.</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="observacoes_triagem" class="form-label">Observações da Triagem <span class="text-muted">(Opcional)</span></label>
                            <textarea name="observacoes_triagem" id="observacoes_triagem" rows="3"
                                      class="form-control auto-resize @error('observacoes_triagem', 'triagem') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;">{{ old('observacoes_triagem', $ocorrencia->observacoes_triagem) }}</textarea>
                            @error('observacoes_triagem', 'triagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Confirmar Triagem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->triagem->any())
        <script>
            new bootstrap.Modal(document.getElementById('modalTriagem')).show();
        </script>
    @endif
@endsection
