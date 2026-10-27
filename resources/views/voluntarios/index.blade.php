@extends('layouts.app')

@section('title', 'Voluntários')

@php
    $disponibilidadeSlug = [
        'Disponível' => 'disponivel',
        'Indisponível' => 'indisponivel',
    ];
@endphp

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Voluntários</h1>
        <button type="button" class="btn btn-primary btn-pill" data-bs-toggle="modal" data-bs-target="#modalNovoVoluntario">
            <i class="bi bi-plus-lg"></i> Novo Voluntário
        </button>
    </div>
    
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <p class="mb-0 text-muted">
            Mostrando {{ $voluntarios->count() }} de {{ $voluntarios->total() }} voluntários
        </p>
        <div class="btn-group" role="group">
            <a href="{{ route('voluntarios.index', ['busca' => $busca]) }}"
               class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }}">Todos</a>
            <a href="{{ route('voluntarios.index', ['busca' => $busca, 'status' => 'ativos']) }}"
               class="btn btn-sm {{ $status === 'ativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Ativos</a>
            <a href="{{ route('voluntarios.index', ['busca' => $busca, 'status' => 'inativos']) }}"
               class="btn btn-sm {{ $status === 'inativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Inativos</a>
        </div>
        <form method="GET" action="{{ route('voluntarios.index') }}" class="search-inline" style="width: 300px; max-width: 100%;">
            <i class="bi bi-search"></i>
            <input type="text" name="busca" value="{{ $busca }}" class="form-control" placeholder="Buscar por nome...">
            <input type="hidden" name="status" value="{{ $status }}">
            @if ($busca)
                <a href="{{ route('voluntarios.index', ['status' => $status]) }}" class="small d-block mt-1 text-muted">Limpar busca</a>
            @endif
        </form>
    </div>

    @if ($voluntarios->isEmpty())
        <div class="equip-card">
            <div class="text-center text-muted py-5">
                <i class="bi bi-person-heart" style="font-size: 2.5rem; color: #cbd5e1;"></i>
                <p class="mt-2 mb-0">
                    @if ($busca && $status)
                        Nenhum voluntário {{ $status === 'ativos' ? 'ativo' : 'inativo' }} encontrado para "{{ $busca }}".
                    @elseif ($busca)
                        Nenhum voluntário encontrado para "{{ $busca }}".
                    @elseif ($status)
                        Nenhum voluntário {{ $status === 'ativos' ? 'ativo' : 'inativo' }} encontrado.
                    @else
                        Nenhum voluntário cadastrado ainda.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($voluntarios as $voluntario)
                <div class="col-md-6 col-lg-4">
                    <div class="equip-card">
                        <div class="equip-card-top">
                            <div class="equip-card-heading">
                                <p class="equip-card-name">{{ $voluntario->nome }}</p>
                                @if ($voluntario->habilidade)
                                    <span class="tag-chip-subtle tag-chip-{{ $voluntario->corHabilidade() }}">{{ $voluntario->habilidade }}</span>
                                @endif
                            </div>
                            <div class="dropdown">
                                <button type="button" class="equip-card-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Mais ações">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('voluntarios.edit', $voluntario) }}">
                                            <i class="bi bi-pencil me-2"></i> Editar
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('voluntarios.toggle-ativo', $voluntario) }}"
                                              @if ($voluntario->ativo) data-confirm="Inativar '{{ $voluntario->nome }}'? Ele deixará de aparecer como disponível para equipes." @endif>
                                            @csrf
                                            @method('PATCH')
                                            @if ($voluntario->ativo)
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
                                    <li>
                                        <form method="POST" action="{{ route('voluntarios.destroy', $voluntario) }}"
                                              data-confirm="Excluir '{{ $voluntario->nome }}'? Essa ação não pode ser desfeita.">
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

                        @if ($voluntario->telefone)
                            <div class="equip-card-meta">
                                <span class="row-item"><i class="bi bi-telephone"></i> {{ $voluntario->telefone }}</span>
                            </div>
                        @endif

                        <div class="equip-card-foot justify-content-end">
                            @if (!$voluntario->ativo)
                                <span class="status-badge status-badge-indisponivel"><i class="bi bi-slash-circle"></i> Inativo</span>
                            @else
                                <form method="POST" action="{{ route('voluntarios.disponibilidade', $voluntario) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="disponibilidade" onchange="this.form.submit()"
                                            class="status-pill-select status-pill-{{ $disponibilidadeSlug[$voluntario->disponibilidade] ?? 'indisponivel' }}">
                                        @foreach ($disponibilidadeOpcoes as $opcao)
                                            <option value="{{ $opcao }}" {{ $voluntario->disponibilidade === $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
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
        {{ $voluntarios->links() }}
    </div>

    <div class="modal fade" id="modalNovoVoluntario" tabindex="-1" aria-labelledby="modalNovoVoluntarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('voluntarios.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNovoVoluntarioLabel">Novo Voluntário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" value="{{ old('nome') }}"
                                   class="form-control @error('nome', 'voluntario') is-invalid @enderror" required autofocus>
                            @error('nome', 'voluntario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefone <span class="text-muted">(Opcional)</span></label>
                                <input type="text" name="telefone" value="{{ old('telefone') }}"
                                       class="form-control mask-telefone @error('telefone', 'voluntario') is-invalid @enderror">
                                @error('telefone', 'voluntario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Habilidade <span class="text-muted">(Opcional)</span></label>
                                <input type="text" name="habilidade" value="{{ old('habilidade') }}" placeholder="Ex: Primeiros socorros"
                                       class="form-control @error('habilidade', 'voluntario') is-invalid @enderror">
                                @error('habilidade', 'voluntario')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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

    @if ($errors->voluntario->any())
        <script>
            new bootstrap.Modal(document.getElementById('modalNovoVoluntario')).show();
        </script>
    @endif
@endsection
