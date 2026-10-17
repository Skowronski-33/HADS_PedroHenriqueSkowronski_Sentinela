@extends('layouts.app')

@section('title', $abrigo->nome)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0">{{ $abrigo->nome }}</h1>
            <p class="text-muted mb-0">{{ $abrigo->endereco }}@if($abrigo->bairro) - {{ $abrigo->bairro }}@endif</p>
        </div>
        <div>
            <a href="{{ route('abrigos.edit', $abrigo) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('abrigos.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">Ocupação Atual</div>
                <div class="card-body">
                    @php
                        $percentual = $abrigo->capacidade > 0 ? min(100, round(($ocupacaoAtual / $abrigo->capacidade) * 100)) : 0;
                        $corBarra = $ocupacaoAtual >= $abrigo->capacidade ? 'bg-danger' : ($percentual >= 80 ? 'bg-warning' : 'bg-success');
                    @endphp
                    <div class="d-flex justify-content-between mb-1">
                        <strong>{{ $ocupacaoAtual }} / {{ $abrigo->capacidade }} pessoas</strong>
                        <span>{{ $percentual }}%</span>
                    </div>
                    <div class="progress mb-3" style="height: 1.5rem;">
                        <div class="progress-bar {{ $corBarra }}" style="width: {{ $percentual }}%"></div>
                    </div>

                    <p class="text-muted small mb-0">Cadastre, dê saída ou transfira pessoas na seção "Pessoas Abrigadas" abaixo.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">Informações</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Responsável:</strong> {{ $abrigo->responsavel ?: '-' }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ $abrigo->status }}</p>
                    <p class="mb-0"><strong>Ativo:</strong> {{ $abrigo->ativo ? 'Sim' : 'Não' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Pessoas Abrigadas</span>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrarPessoa">
                <i class="bi bi-person-plus"></i> Cadastrar Pessoa
            </button>
        </div>
        <div class="card-body border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="mb-0 text-muted">
                    Mostrando {{ $pessoas->count() }} de {{ $pessoas->total() }} pessoas abrigadas
                    (Capacidade: {{ $abrigo->capacidade }})
                </p>
                <form method="GET" action="{{ route('abrigos.show', $abrigo) }}" class="d-flex gap-2">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por nome...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">
                        <i class="bi bi-search"></i>
                    </button>
                    @if ($busca)
                        <a href="{{ route('abrigos.show', $abrigo) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Idade</th>
                        <th>Contato</th>
                        <th>Família</th>
                        <th>Entrada em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pessoas as $pessoa)
                        <tr>
                            <td>
                                {{ $pessoa->nome }}
                                @if ($pessoa->necessita_prioridade)
                                    <span class="badge bg-warning text-dark" title="Necessita de acessibilidade ou prioridade especial">
                                        <i class="bi bi-star-fill"></i> Prioridade
                                    </span>
                                @endif
                            </td>
                            <td>{{ $pessoa->idade ?? '-' }}</td>
                            <td>{{ $pessoa->contato ?: '-' }}</td>
                            <td>
                                @if ($pessoa->responsavelFamiliar)
                                    <small class="text-muted">Dependente de {{ $pessoa->responsavelFamiliar->nome }}</small>
                                @elseif ($pessoa->dependentes->isNotEmpty())
                                    <small class="text-muted">Responsável por {{ $pessoa->dependentes->count() }}</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $pessoa->data_entrada->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    @if ($outrosAbrigos->isNotEmpty())
                                        <form method="POST" action="{{ route('abrigos.pessoas.transferir', [$abrigo, $pessoa]) }}" class="d-flex gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <select name="abrigo_destino_id" class="form-select form-select-sm" style="width: 160px;" required>
                                                <option value="">Transferir para...</option>
                                                @foreach ($outrosAbrigos as $outro)
                                                    <option value="{{ $outro->id }}">{{ $outro->nome }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('abrigos.pessoas.saida', [$abrigo, $pessoa]) }}"
                                          onsubmit="return confirm('Registrar saída definitiva de {{ $pessoa->nome }} deste abrigo?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                            <i class="bi bi-box-arrow-right"></i> Saída
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                @if ($busca)
                                    Nenhuma pessoa encontrada para "{{ $busca }}".
                                @else
                                    Nenhuma pessoa abrigada no momento.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pessoas->hasPages())
            <div class="card-body border-top">
                {{ $pessoas->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalCadastrarPessoa" tabindex="-1" aria-labelledby="modalCadastrarPessoaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('abrigos.pessoas.store', $abrigo) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCadastrarPessoaLabel">Cadastrar Pessoa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Apenas dados simples e necessários para identificação — evite informações sensíveis (saúde, etnia, religião etc.), conforme a LGPD.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" value="{{ old('nome') }}"
                                       class="form-control @error('nome', 'pessoa') is-invalid @enderror" required autofocus>
                                @error('nome', 'pessoa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Idade</label>
                                <input type="number" name="idade" min="0" max="120" value="{{ old('idade') }}"
                                       class="form-control @error('idade', 'pessoa') is-invalid @enderror">
                                @error('idade', 'pessoa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Contato <span class="text-muted">(Opcional)</span></label>
                                <input type="text" name="contato" value="{{ old('contato') }}" placeholder="(XX) XXXXX-XXXX"
                                       class="form-control mask-telefone @error('contato', 'pessoa') is-invalid @enderror">
                                @error('contato', 'pessoa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Faz parte do grupo familiar de <span class="text-muted">(Opcional)</span></label>
                            <select name="responsavel_familiar_id" class="form-select @error('responsavel_familiar_id', 'pessoa') is-invalid @enderror">
                                <option value="">Nenhum (é o responsável pela própria família, ou veio sozinho)</option>
                                @foreach ($responsaveisDisponiveis as $responsavel)
                                    <option value="{{ $responsavel->id }}" {{ old('responsavel_familiar_id') == $responsavel->id ? 'selected' : '' }}>
                                        {{ $responsavel->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('responsavel_familiar_id', 'pessoa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Use isso para agrupar famílias: cadastre o responsável primeiro, depois vincule os demais a ele.</div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="necessita_prioridade" value="1" id="necessita_prioridade" class="form-check-input"
                                   {{ old('necessita_prioridade') ? 'checked' : '' }}>
                            <label for="necessita_prioridade" class="form-check-label">
                                Necessita de acessibilidade ou prioridade especial (idoso, gestante, PCD, criança desacompanhada etc.)
                            </label>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" rows="2"
                                      class="form-control auto-resize @error('observacoes', 'pessoa') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;">{{ old('observacoes') }}</textarea>
                            @error('observacoes', 'pessoa')
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

    @if ($errors->pessoa->any())
        <script>
            new bootstrap.Modal(document.getElementById('modalCadastrarPessoa')).show();
        </script>
    @endif

    <div class="card mb-4">
        <div class="card-header">Donativos</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Unidade</th>
                        <th>Estoque Atual</th>
                        <th class="text-end">Movimentar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($abrigo->donativos as $donativo)
                        <tr>
                            <td>
                                {{ $donativo->tipo }}
                                @if ($donativo->descricao)
                                    <br><small class="text-muted">{{ $donativo->descricao }}</small>
                                @endif
                            </td>
                            <td>{{ $donativo->unidade_medida }}</td>
                            <td>
                                <span class="badge {{ $donativo->estoque_atual > 0 ? 'bg-info' : 'bg-secondary' }}">
                                    {{ $donativo->estoque_atual }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form method="POST" action="{{ route('abrigos.donativos.entrada', [$abrigo, $donativo]) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantidade" min="1" class="form-control form-control-sm" style="width: 80px;" placeholder="Qtd." required>
                                        <button type="submit" class="btn btn-sm btn-success text-nowrap">Entrada</button>
                                    </form>
                                    <form method="POST" action="{{ route('abrigos.donativos.saida', [$abrigo, $donativo]) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantidade" min="1" class="form-control form-control-sm" style="width: 80px;" placeholder="Qtd." required>
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">Saída</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nenhum item de donativo cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Cadastrar Novo Item de Donativo</div>
        <div class="card-body">
            <form method="POST" action="{{ route('abrigos.donativos.store', $abrigo) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Item</label>
                    <input type="text" name="tipo" class="form-control" placeholder="Ex: Água mineral" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Descrição</label>
                    <input type="text" name="descricao" class="form-control" placeholder="Opcional">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unidade</label>
                    <input type="text" name="unidade_medida" class="form-control" placeholder="Ex: litros, un" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
