@extends('layouts.app')

@section('title', 'Voluntários')

@php
    $disponibilidadeSlug = [
        'Disponível' => 'disponivel',
        'Indisponível' => 'indisponivel',
    ];
@endphp

@section('content')
    <x-page-header title="Voluntários" icon="bi-person-heart" modal-target="modalNovoVoluntario" action-text="Novo Voluntário" />

    <x-filter-bar
        route-name="voluntarios.index"
        :search="$busca"
        :status="$status"
        search-placeholder="Buscar por nome..."
        label="Mostrando {{ $voluntarios->count() }} de {{ $voluntarios->total() }} voluntários"
    />

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
                @php
                    $telDigits = $voluntario->telefone ? preg_replace('/\D/', '', $voluntario->telefone) : null;
                @endphp
                <div class="col-md-6 col-lg-4">
                    <x-resource-card
                        :title="$voluntario->nome"
                        :subtitle="$voluntario->habilidade"
                        :border-color="!$voluntario->ativo ? 'indisponivel' : ($disponibilidadeSlug[$voluntario->disponibilidade] ?? 'indisponivel')"
                    >
                        <x-slot:actions>
                            <li>
                                <a class="dropdown-item" href="{{ route('voluntarios.edit', $voluntario) }}">
                                    <i class="bi bi-pencil me-2"></i> Editar
                                </a>
                            </li>
                            @if (!$voluntario->ativo)
                                <li>
                                    <form method="POST" action="{{ route('voluntarios.toggle-ativo', $voluntario) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-check-circle me-2"></i> Reativar
                                        </button>
                                    </form>
                                </li>
                            @endif
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
                        </x-slot:actions>

                        @if ($voluntario->equipeAtual)
                            <div class="equip-card-context">
                                <i class="bi bi-broadcast"></i>
                                <span>
                                    {{ $voluntario->equipeAtual->nome }}
                                    @if ($voluntario->atendimentoAtual?->ocorrencia)
                                        — Ocorrência #{{ $voluntario->atendimentoAtual->ocorrencia->id }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        <x-slot:footer>
                            <div class="d-flex justify-content-between align-items-center w-100">
                                @if ($telDigits)
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="tel:{{ $telDigits }}" class="row-item" title="Ligar para {{ $voluntario->nome }}">
                                            <i class="bi bi-telephone-fill"></i> {{ $voluntario->telefone }}
                                        </a>
                                        <a href="https://wa.me/55{{ $telDigits }}" target="_blank" rel="noopener"
                                           class="text-success" title="Chamar no WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="equip-card-inactive">Sem telefone</span>
                                @endif

                                @if (!$voluntario->ativo)
                                    <x-status-badge text="Inativo" color="bg-danger" icon="bi-slash-circle" />
                                @else
                                    <div class="dropdown">
                                        <button type="button"
                                                class="status-badge status-badge-btn status-badge-{{ $disponibilidadeSlug[$voluntario->disponibilidade] ?? 'indisponivel' }} border-0"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ $voluntario->disponibilidade }} <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <form method="POST" action="{{ route('voluntarios.disponibilidade', $voluntario) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" name="disponibilidade" value="Disponível" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle-fill me-2"></i> Disponível
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('voluntarios.disponibilidade', $voluntario) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" name="disponibilidade" value="Indisponível" class="dropdown-item text-secondary">
                                                        <i class="bi bi-dash-circle-fill me-2"></i> Indisponível
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ route('voluntarios.toggle-ativo', $voluntario) }}"
                                                      data-confirm="Inativar '{{ $voluntario->nome }}'? Ele deixará de aparecer como disponível para equipes.">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-power me-2"></i> Inativo
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </x-slot:footer>
                    </x-resource-card>
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
