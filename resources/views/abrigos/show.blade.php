@extends('layouts.app')

@section('title', $abrigo->nome)

@section('content')
    <x-page-header icon="bi-house-door-fill" :title="$abrigo->nome">
        <x-slot:subtitle><i class="bi bi-geo-alt"></i> {{ $abrigo->enderecoCompleto() }}</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('abrigos.edit', $abrigo) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="{{ route('abrigos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-people-fill"></i> Ocupação Atual</div>
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
                <div class="card-header"><i class="bi bi-info-circle"></i> Informações</div>
                <div class="card-body">
                    <p class="mb-2"><i class="bi bi-person-badge text-muted"></i> <strong>Responsável:</strong> {{ $abrigo->responsavel ?: '-' }}</p>
                    <p class="mb-2"><i class="bi bi-telephone text-muted"></i> <strong>Telefone:</strong> {{ $abrigo->telefone_responsavel ?: '-' }}</p>
                    <p class="mb-2"><i class="bi bi-flag text-muted"></i> <strong>Status:</strong> {{ $abrigo->status }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people-fill"></i> Pessoas Abrigadas</span>
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
                <form method="GET" action="{{ route('abrigos.show', $abrigo) }}" class="d-flex align-items-center gap-2">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por nome...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">
                        <i class="bi bi-search"></i>
                    </button>
                    @if ($busca)
                        <button type="button" data-href="{{ route('abrigos.show', $abrigo) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</button>
                    @endif

                    <label for="porPaginaPessoas" class="text-muted small mb-0 ms-2 text-nowrap">Mostrar</label>
                    <select name="por_pagina" id="porPaginaPessoas" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        @foreach ($porPaginaOpcoes as $opcao)
                            <option value="{{ $opcao }}" {{ $porPagina === (string) $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                        @endforeach
                        <option value="todos" {{ $porPagina === 'todos' ? 'selected' : '' }}>Todos</option>
                    </select>
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
                                @php
                                    $temFamilia = $pessoa->responsavelFamiliar || $pessoa->dependentes->isNotEmpty();
                                @endphp
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar dados"
                                            onclick="abrirModalEditarPessoa({{ $pessoa->id }}, {{ json_encode($pessoa->nome) }}, {{ $pessoa->idade ?? 'null' }}, {{ json_encode($pessoa->contato) }}, {{ $pessoa->necessita_prioridade ? 'true' : 'false' }}, {{ $pessoa->responsavel_familiar_id ?? 'null' }}, {{ json_encode($pessoa->observacoes) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if ($outrosAbrigos->isNotEmpty())
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="Transferir"
                                                onclick="abrirModalTransferir({{ $pessoa->id }}, {{ json_encode($pessoa->nome) }}, {{ $temFamilia ? 'true' : 'false' }}, {{ $pessoa->responsavelFamiliar ? 'true' : 'false' }}, {{ $pessoa->dependentes->count() }})">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('abrigos.pessoas.saida', [$abrigo, $pessoa]) }}"
                                          data-confirm="{{ $temFamilia ? "Registrar saída definitiva de {$pessoa->nome} e de toda a família (responsável + dependentes) deste abrigo?" : "Registrar saída definitiva de {$pessoa->nome} deste abrigo?" }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap"
                                                title="{{ $temFamilia ? 'A família inteira (responsável + dependentes) sai junto automaticamente.' : 'Saída' }}">
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

    <div class="modal fade" id="modalEditarPessoa" tabindex="-1" aria-labelledby="modalEditarPessoaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="formEditarPessoa" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_pessoa_id" id="editarPessoaId" value="{{ old('_pessoa_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarPessoaLabel">Editar Pessoa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="nome" id="editarPessoaNome"
                                       class="form-control @error('nome', 'pessoa_edit') is-invalid @enderror" required>
                                @error('nome', 'pessoa_edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Idade</label>
                                <input type="number" name="idade" id="editarPessoaIdade" min="0" max="120"
                                       class="form-control @error('idade', 'pessoa_edit') is-invalid @enderror">
                                @error('idade', 'pessoa_edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Contato <span class="text-muted">(Opcional)</span></label>
                                <input type="text" name="contato" id="editarPessoaContato" placeholder="(XX) XXXXX-XXXX"
                                       class="form-control mask-telefone @error('contato', 'pessoa_edit') is-invalid @enderror">
                                @error('contato', 'pessoa_edit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Faz parte do grupo familiar de <span class="text-muted">(Opcional)</span></label>
                            <select name="responsavel_familiar_id" id="editarPessoaResponsavel" class="form-select @error('responsavel_familiar_id', 'pessoa_edit') is-invalid @enderror">
                                <option value="">Nenhum (é o responsável pela própria família, ou veio sozinho)</option>
                                @foreach ($responsaveisDisponiveis as $responsavel)
                                    <option value="{{ $responsavel->id }}" data-pessoa-id="{{ $responsavel->id }}">{{ $responsavel->nome }}</option>
                                @endforeach
                            </select>
                            @error('responsavel_familiar_id', 'pessoa_edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="necessita_prioridade" value="1" id="editarPessoaPrioridade" class="form-check-input">
                            <label for="editarPessoaPrioridade" class="form-check-label">
                                Necessita de acessibilidade ou prioridade especial (idoso, gestante, PCD, criança desacompanhada etc.)
                            </label>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" id="editarPessoaObservacoes" rows="2"
                                      class="form-control auto-resize @error('observacoes', 'pessoa_edit') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;"></textarea>
                            @error('observacoes', 'pessoa_edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTransferirPessoa" tabindex="-1" aria-labelledby="modalTransferirPessoaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formTransferirPessoa" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_pessoa_id" id="transferirPessoaId" value="{{ old('_pessoa_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTransferirPessoaLabel">Transferir Pessoa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Transferir <strong id="transferirPessoaNome"></strong> para outro abrigo.</p>

                        <div class="mb-3">
                            <label class="form-label">Abrigo de destino</label>
                            <select name="abrigo_destino_id" id="transferirAbrigoDestino"
                                    class="form-select @error('abrigo_destino_id', 'pessoa_transferir') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                @foreach ($outrosAbrigos as $outro)
                                    <option value="{{ $outro->id }}">{{ $outro->nome }}</option>
                                @endforeach
                            </select>
                            @error('abrigo_destino_id', 'pessoa_transferir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check" id="transferirFamiliaCheck" hidden>
                            <input type="checkbox" name="incluir_familia" value="1" id="transferirIncluirFamilia" class="form-check-input" checked>
                            <label for="transferirIncluirFamilia" class="form-check-label" id="transferirFamiliaLabel"></label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Transferir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const pessoaTransferirUrlTemplate = @json(route('abrigos.pessoas.transferir', [$abrigo, '__ID__']));

        function abrirModalTransferir(id, nome, temFamilia, souDependente, qtdDependentes) {
            document.getElementById('formTransferirPessoa').action = pessoaTransferirUrlTemplate.replace('__ID__', id);
            document.getElementById('transferirPessoaId').value = id;
            document.getElementById('transferirPessoaNome').textContent = nome;
            document.getElementById('transferirAbrigoDestino').value = '';

            const check = document.getElementById('transferirFamiliaCheck');
            const label = document.getElementById('transferirFamiliaLabel');
            const input = document.getElementById('transferirIncluirFamilia');

            check.hidden = !temFamilia;
            input.checked = true;
            label.textContent = souDependente
                ? 'Transferir também o restante da família (responsável e demais dependentes)'
                : `Transferir também ${qtdDependentes} dependente(s) junto`;

            new bootstrap.Modal(document.getElementById('modalTransferirPessoa')).show();
        }
    </script>

    @php
        $pessoaComErroTransferencia = old('_pessoa_id')
            ? $pessoas->firstWhere('id', (int) old('_pessoa_id'))
            : null;
    @endphp
    @if ($errors->pessoa_transferir->any())
        <script>
            @if ($pessoaComErroTransferencia)
                abrirModalTransferir(
                    {{ $pessoaComErroTransferencia->id }},
                    {{ json_encode($pessoaComErroTransferencia->nome) }},
                    {{ ($pessoaComErroTransferencia->responsavelFamiliar || $pessoaComErroTransferencia->dependentes->isNotEmpty()) ? 'true' : 'false' }},
                    {{ $pessoaComErroTransferencia->responsavelFamiliar ? 'true' : 'false' }},
                    {{ $pessoaComErroTransferencia->dependentes->count() }}
                );
            @else
                new bootstrap.Modal(document.getElementById('modalTransferirPessoa')).show();
            @endif
            document.getElementById('transferirAbrigoDestino').value = {{ json_encode(old('abrigo_destino_id')) }};
            document.getElementById('transferirIncluirFamilia').checked = {{ old('incluir_familia') ? 'true' : 'false' }};
        </script>
    @endif

    <script>
        const pessoaUpdateUrlTemplate = @json(route('abrigos.pessoas.update', [$abrigo, '__ID__']));

        function abrirModalEditarPessoa(id, nome, idade, contato, necessitaPrioridade, responsavelId, observacoes) {
            document.getElementById('formEditarPessoa').action = pessoaUpdateUrlTemplate.replace('__ID__', id);
            document.getElementById('editarPessoaId').value = id;
            document.getElementById('editarPessoaNome').value = nome;
            document.getElementById('editarPessoaIdade').value = idade ?? '';
            document.getElementById('editarPessoaContato').value = contato || '';
            document.getElementById('editarPessoaPrioridade').checked = !!necessitaPrioridade;
            document.getElementById('editarPessoaObservacoes').value = observacoes || '';

            const select = document.getElementById('editarPessoaResponsavel');
            Array.from(select.options).forEach((opt) => {
                // Ninguém pode ser responsável de si mesmo.
                opt.hidden = opt.dataset.pessoaId && Number(opt.dataset.pessoaId) === Number(id);
            });
            select.value = responsavelId ?? '';

            new bootstrap.Modal(document.getElementById('modalEditarPessoa')).show();
        }
    </script>

    @if ($errors->pessoa_edit->any())
        <script>
            abrirModalEditarPessoa(
                {{ old('_pessoa_id', 0) }},
                {{ json_encode(old('nome')) }},
                {{ old('idade', 'null') }},
                {{ json_encode(old('contato')) }},
                {{ old('necessita_prioridade') ? 'true' : 'false' }},
                {{ old('responsavel_familiar_id', 'null') }},
                {{ json_encode(old('observacoes')) }}
            );
        </script>
    @endif

    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="entity-card-icon" style="background: var(--sentinela-navy-800); width: 2.75rem; height: 2.75rem; border-radius: .5rem; display:flex; align-items:center; justify-content:center; color: var(--sentinela-amber-500); font-size: 1.3rem;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Donativos</h5>
                    <p class="text-muted mb-0">
                        {{ $abrigo->donativos->count() }} {{ Str::plural('item', $abrigo->donativos->count()) }} cadastrado{{ $abrigo->donativos->count() == 1 ? '' : 's' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('abrigos.donativos.index', $abrigo) }}" class="btn btn-primary">
                <i class="bi bi-box-seam"></i> Gerenciar Donativos
            </a>
        </div>
    </div>
@endsection
