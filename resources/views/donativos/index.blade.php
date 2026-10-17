@extends('layouts.app')

@section('title', 'Donativos - '.$abrigo->nome)

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="mb-1">Donativos</h1>
            <p class="text-muted mb-0">{{ $abrigo->nome }}</p>
        </div>
        <div>
            <a href="{{ route('abrigos.show', $abrigo) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Voltar ao Abrigo
            </a>
        </div>
    </div>

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
                <form method="GET" action="{{ route('abrigos.donativos.index', $abrigo) }}" class="d-flex gap-2">
                    <input type="text" name="busca" value="{{ $busca }}" class="form-control form-control-sm" placeholder="Buscar por item...">
                    <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">
                        <i class="bi bi-search"></i>
                    </button>
                    @if ($busca)
                        <a href="{{ route('abrigos.donativos.index', $abrigo) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Limpar</a>
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
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="abrirModalEditarDonativo({{ $donativo->id }}, {{ json_encode($donativo->tipo) }}, {{ json_encode($donativo->descricao) }}, {{ json_encode($donativo->unidade_medida) }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('abrigos.donativos.entrada', [$abrigo, $donativo]) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantidade" min="1" class="form-control form-control-sm" style="width: 70px;" placeholder="Qtd." required>
                                        <button type="submit" class="btn btn-sm btn-success text-nowrap" title="Registrar entrada (recebimento)">
                                            <i class="bi bi-box-arrow-in-down"></i> Entrada
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('abrigos.donativos.saida', [$abrigo, $donativo]) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantidade" min="1" class="form-control form-control-sm" style="width: 70px;" placeholder="Qtd." required>
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">Saída</button>
                                    </form>
                                    @if ($outrosAbrigos->isNotEmpty())
                                        <form method="POST" action="{{ route('abrigos.donativos.transferir', [$abrigo, $donativo]) }}" class="d-flex gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantidade" min="1" class="form-control form-control-sm" style="width: 70px;" placeholder="Qtd." required>
                                            <select name="abrigo_destino_id" class="form-select form-select-sm" style="width: 150px;" required>
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

    <script>
        const donativoUpdateUrlTemplate = @json(route('abrigos.donativos.update', [$abrigo, '__ID__']));

        function abrirModalEditarDonativo(id, tipo, descricao, unidade) {
            document.getElementById('formEditarDonativo').action = donativoUpdateUrlTemplate.replace('__ID__', id);
            document.getElementById('editarDonativoId').value = id;
            document.getElementById('editarDonativoTipo').value = tipo;
            document.getElementById('editarDonativoDescricao').value = descricao || '';
            document.getElementById('editarDonativoUnidade').value = unidade;
            new bootstrap.Modal(document.getElementById('modalEditarDonativo')).show();
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
