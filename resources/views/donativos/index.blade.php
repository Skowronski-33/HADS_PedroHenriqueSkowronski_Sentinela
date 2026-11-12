@extends('layouts.app')

@section('title', 'Donativos - '.$abrigo->nome)

@section('content')
    <x-page-header
        title="Donativos"
        icon="bi-box-seam"
        :subtitle="$abrigo->nome"
        action-route="abrigos.show"
        :action-params="$abrigo"
        action-text="Voltar ao Abrigo"
        action-icon="bi-arrow-left"
        action-variant="outline-secondary"
    />

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Itens Cadastrados</span>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoDonativo">
                <i class="bi bi-plus-lg"></i> Novo Item
            </button>
        </div>
        <div class="card-body border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <p class="mb-0 text-muted">
                    Mostrando {{ $donativos->count() }} de {{ $donativos->total() }} itens
                </p>
                <form method="GET" action="{{ route('abrigos.donativos.index', $abrigo) }}" class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 260px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Buscar por item...">
                        <button type="submit" class="btn btn-outline-secondary" title="Buscar"><i class="bi bi-search"></i></button>
                    </div>
                    @if ($busca)
                        <button type="button" data-href="{{ route('abrigos.donativos.index', $abrigo) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</button>
                    @endif
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Unidade</th>
                        <th>Estoque Atual</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($donativos as $donativo)
                        @php
                            $corEstoque = match (true) {
                                $donativo->estoque_atual <= 0 => 'bg-danger',
                                $donativo->estoque_atual <= 10 => 'bg-warning text-dark',
                                default => 'bg-info',
                            };
                        @endphp
                        <tr>
                            <td>
                                {{ $donativo->tipo }}
                                @if ($donativo->descricao)
                                    <br><small class="text-muted">{{ $donativo->descricao }}</small>
                                @endif
                            </td>
                            <td>{{ $donativo->unidade_medida }}</td>
                            <td>
                                <span class="badge {{ $corEstoque }}">{{ $donativo->estoque_atual }}</span>
                                @if ($donativo->estoque_atual <= 0)
                                    <br><small class="text-danger">Esgotado</small>
                                @elseif ($donativo->estoque_atual <= 10)
                                    <br><small class="text-warning">Estoque baixo</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar item"
                                            onclick="abrirModalEditarDonativo({{ $donativo->id }}, {{ json_encode($donativo->tipo) }}, {{ json_encode($donativo->descricao) }}, {{ json_encode($donativo->unidade_medida) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success text-nowrap" title="Registrar entrada (recebimento)"
                                            onclick="abrirModalEntrada({{ $donativo->id }}, {{ json_encode($donativo->tipo) }}, {{ json_encode($donativo->unidade_medida) }})">
                                        <i class="bi bi-box-arrow-in-down"></i> Entrada
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" title="Registrar saída"
                                            onclick="abrirModalSaida({{ $donativo->id }}, {{ json_encode($donativo->tipo) }}, {{ $donativo->estoque_atual }}, {{ json_encode($donativo->unidade_medida) }})">
                                        <i class="bi bi-box-arrow-up"></i> Saída
                                    </button>
                                    @if ($outrosAbrigos->isNotEmpty())
                                        <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" title="Transferir para outro abrigo"
                                                onclick="abrirModalTransferir({{ $donativo->id }}, {{ json_encode($donativo->tipo) }}, {{ $donativo->estoque_atual }}, {{ json_encode($donativo->unidade_medida) }})">
                                            <i class="bi bi-arrow-left-right"></i> Transferir
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                @if ($busca)
                                    Nenhum item encontrado para "{{ $busca }}".
                                @else
                                    Nenhum item de donativo cadastrado.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($donativos->hasPages())
            <div class="card-body border-top">
                {{ $donativos->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalNovoDonativo" tabindex="-1" aria-labelledby="modalNovoDonativoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('abrigos.donativos.store', $abrigo) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNovoDonativoLabel">Novo Item de Donativo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Item</label>
                            <input type="text" name="tipo" value="{{ old('tipo') }}" placeholder="Ex: Água mineral"
                                   class="form-control @error('tipo', 'donativo') is-invalid @enderror" required autofocus>
                            @error('tipo', 'donativo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição <span class="text-muted">(Opcional)</span></label>
                            <input type="text" name="descricao" value="{{ old('descricao') }}"
                                   class="form-control @error('descricao', 'donativo') is-invalid @enderror">
                            @error('descricao', 'donativo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Unidade</label>
                            <select name="unidade_medida" class="form-select @error('unidade_medida', 'donativo') is-invalid @enderror" required>
                                @foreach ($unidadeOpcoes as $opcao)
                                    <option value="{{ $opcao }}" {{ old('unidade_medida', 'Unidades') === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                @endforeach
                            </select>
                            @error('unidade_medida', 'donativo')
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

    <div class="modal fade" id="modalEditarDonativo" tabindex="-1" aria-labelledby="modalEditarDonativoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formEditarDonativo" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_donativo_id" id="editarDonativoId" value="{{ old('_donativo_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarDonativoLabel">Editar Item de Donativo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Item</label>
                            <input type="text" name="tipo" id="editarDonativoTipo"
                                   class="form-control @error('tipo', 'donativo_edit') is-invalid @enderror" required>
                            @error('tipo', 'donativo_edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrição <span class="text-muted">(Opcional)</span></label>
                            <input type="text" name="descricao" id="editarDonativoDescricao"
                                   class="form-control @error('descricao', 'donativo_edit') is-invalid @enderror">
                            @error('descricao', 'donativo_edit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Unidade</label>
                            <select name="unidade_medida" id="editarDonativoUnidade" class="form-select @error('unidade_medida', 'donativo_edit') is-invalid @enderror" required>
                                @foreach ($unidadeOpcoes as $opcao)
                                    <option value="{{ $opcao }}">{{ $opcao }}</option>
                                @endforeach
                            </select>
                            @error('unidade_medida', 'donativo_edit')
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

    <div class="modal fade" id="modalEntrada" tabindex="-1" aria-labelledby="modalEntradaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formEntrada" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEntradaLabel">
                            <i class="bi bi-box-arrow-in-down text-success"></i> Registrar Entrada: <span id="entradaItemNome"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Quantidade</label>
                            <div class="input-group">
                                <input type="number" name="quantidade" min="1"
                                       class="form-control @error('quantidade', 'donativo_entrada') is-invalid @enderror" required autofocus>
                                <span class="input-group-text" id="entradaUnidade"></span>
                            </div>
                            @error('quantidade', 'donativo_entrada')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data <span class="text-muted">(Opcional — assume agora se vazio)</span></label>
                            <input type="date" name="data_hora" class="form-control @error('data_hora', 'donativo_entrada') is-invalid @enderror">
                            @error('data_hora', 'donativo_entrada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observações <span class="text-muted">(Opcional)</span></label>
                            <textarea name="observacoes" rows="2"
                                      class="form-control auto-resize @error('observacoes', 'donativo_entrada') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;"></textarea>
                            @error('observacoes', 'donativo_entrada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Confirmar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSaida" tabindex="-1" aria-labelledby="modalSaidaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formSaida" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSaidaLabel">
                            <i class="bi bi-box-arrow-up text-danger"></i> Registrar Saída: <span id="saidaItemNome"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Estoque atual: <strong id="saidaEstoqueAtual"></strong></p>
                        <div class="mb-3">
                            <label class="form-label">Quantidade</label>
                            <div class="input-group">
                                <input type="number" name="quantidade" id="saidaQuantidadeInput" min="1"
                                       class="form-control @error('quantidade', 'donativo_saida') is-invalid @enderror" required autofocus>
                                <span class="input-group-text" id="saidaUnidade"></span>
                            </div>
                            @error('quantidade', 'donativo_saida')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Data <span class="text-muted">(Opcional — assume agora se vazio)</span></label>
                            <input type="date" name="data_hora" class="form-control @error('data_hora', 'donativo_saida') is-invalid @enderror">
                            @error('data_hora', 'donativo_saida')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observações <span class="text-muted">(Opcional)</span></label>
                            <textarea name="observacoes" rows="2"
                                      class="form-control auto-resize @error('observacoes', 'donativo_saida') is-invalid @enderror"
                                      style="resize: none; overflow: hidden;"></textarea>
                            @error('observacoes', 'donativo_saida')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-check-lg"></i> Confirmar Saída</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($outrosAbrigos->isNotEmpty())
        <div class="modal fade" id="modalTransferir" tabindex="-1" aria-labelledby="modalTransferirLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="formTransferir" action="">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTransferirLabel">
                                <i class="bi bi-arrow-left-right text-primary"></i> Transferir: <span id="transferirItemNome"></span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Estoque atual: <strong id="transferirEstoqueAtual"></strong></p>
                            <div class="mb-3">
                                <label class="form-label">Abrigo de destino</label>
                                <select name="abrigo_destino_id"
                                        class="form-select @error('abrigo_destino_id', 'donativo_transferir') is-invalid @enderror" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($outrosAbrigos as $outro)
                                        <option value="{{ $outro->id }}">{{ $outro->nome }}</option>
                                    @endforeach
                                </select>
                                @error('abrigo_destino_id', 'donativo_transferir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantidade</label>
                                <div class="input-group">
                                    <input type="number" name="quantidade" id="transferirQuantidadeInput" min="1"
                                           class="form-control @error('quantidade', 'donativo_transferir') is-invalid @enderror" required>
                                    <span class="input-group-text" id="transferirUnidade"></span>
                                </div>
                                @error('quantidade', 'donativo_transferir')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Observações <span class="text-muted">(Opcional)</span></label>
                                <textarea name="observacoes" rows="2"
                                          class="form-control auto-resize @error('observacoes', 'donativo_transferir') is-invalid @enderror"
                                          style="resize: none; overflow: hidden;"></textarea>
                                @error('observacoes', 'donativo_transferir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Confirmar Transferência</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        const donativoUpdateUrlTemplate = @json(route('abrigos.donativos.update', [$abrigo, '__ID__']));
        const donativoEntradaUrlTemplate = @json(route('abrigos.donativos.entrada', [$abrigo, '__ID__']));
        const donativoSaidaUrlTemplate = @json(route('abrigos.donativos.saida', [$abrigo, '__ID__']));
        const donativoTransferirUrlTemplate = @json(route('abrigos.donativos.transferir', [$abrigo, '__ID__']));

        function abrirModalEditarDonativo(id, tipo, descricao, unidade) {
            document.getElementById('formEditarDonativo').action = donativoUpdateUrlTemplate.replace('__ID__', id);
            document.getElementById('editarDonativoId').value = id;
            document.getElementById('editarDonativoTipo').value = tipo;
            document.getElementById('editarDonativoDescricao').value = descricao || '';
            document.getElementById('editarDonativoUnidade').value = unidade;
            new bootstrap.Modal(document.getElementById('modalEditarDonativo')).show();
        }

        function abrirModalEntrada(id, nome, unidade) {
            const form = document.getElementById('formEntrada');
            form.reset();
            form.action = donativoEntradaUrlTemplate.replace('__ID__', id);
            document.getElementById('entradaItemNome').textContent = nome;
            document.getElementById('entradaUnidade').textContent = unidade;
            new bootstrap.Modal(document.getElementById('modalEntrada')).show();
        }

        function abrirModalSaida(id, nome, estoque, unidade) {
            const form = document.getElementById('formSaida');
            form.reset();
            form.action = donativoSaidaUrlTemplate.replace('__ID__', id);
            document.getElementById('saidaItemNome').textContent = nome;
            document.getElementById('saidaEstoqueAtual').textContent = estoque + ' ' + unidade;
            document.getElementById('saidaUnidade').textContent = unidade;
            document.getElementById('saidaQuantidadeInput').max = estoque;
            new bootstrap.Modal(document.getElementById('modalSaida')).show();
        }

        function abrirModalTransferir(id, nome, estoque, unidade) {
            const form = document.getElementById('formTransferir');
            if (!form) return;
            form.reset();
            form.action = donativoTransferirUrlTemplate.replace('__ID__', id);
            document.getElementById('transferirItemNome').textContent = nome;
            document.getElementById('transferirEstoqueAtual').textContent = estoque + ' ' + unidade;
            document.getElementById('transferirUnidade').textContent = unidade;
            document.getElementById('transferirQuantidadeInput').max = estoque;
            new bootstrap.Modal(document.getElementById('modalTransferir')).show();
        }
    </script>

    @if ($errors->donativo->any())
        <script>
            new bootstrap.Modal(document.getElementById('modalNovoDonativo')).show();
        </script>
    @endif

    @if ($errors->donativo_edit->any())
        <script>
            abrirModalEditarDonativo(
                {{ old('_donativo_id', 0) }},
                {{ json_encode(old('tipo')) }},
                {{ json_encode(old('descricao')) }},
                {{ json_encode(old('unidade_medida')) }}
            );
        </script>
    @endif
@endsection
