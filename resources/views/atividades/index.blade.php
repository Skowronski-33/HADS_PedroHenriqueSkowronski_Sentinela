@extends('layouts.app')

@section('title', 'Atividades')

@section('content')
    <x-page-header title="Atividades" icon="bi-clock-history"
        subtitle="Registro de ações dos usuários no sistema, para fins de auditoria." />

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('atividades.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="busca" value="{{ $busca }}" class="form-control"
                                   placeholder="Buscar na descrição...">
                            <button type="submit" class="btn btn-outline-secondary" title="Buscar">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Usuário</label>
                        <select name="usuario_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos os usuários</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" {{ (int) $usuarioId === $usuario->id ? 'selected' : '' }}>{{ $usuario->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Período</label>
                        <div class="input-group">
                            <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="form-control" title="Data inicial">
                            <span class="input-group-text">até</span>
                            <input type="date" name="data_fim" value="{{ $dataFim }}" class="form-control" title="Data final">
                        </div>
                    </div>
                </div>

                @if ($busca || $usuarioId || $dataInicio || $dataFim)
                    <a data-href="{{ route('atividades.index') }}" class="small d-inline-block mt-2 text-muted" role="link" tabindex="0">
                        <i class="bi bi-x-circle"></i> Limpar filtros
                    </a>
                @endif
            </form>
        </div>
        <div class="card-body border-bottom py-2">
            <p class="mb-0 text-muted small">
                Mostrando {{ $atividades->count() }} de {{ $atividades->total() }} registros
            </p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($atividades as $atividade)
                        <tr>
                            <td class="text-nowrap">{{ $atividade->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $atividade->usuario->name ?? 'Sistema' }}</td>
                            <td><span class="tag-chip-subtle">{{ $atividade->acao }}</span></td>
                            <td>{{ $atividade->descricao }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                @if ($busca || $usuarioId || $dataInicio || $dataFim)
                                    Nenhum registro encontrado para os filtros aplicados.
                                @else
                                    Nenhuma atividade registrada ainda.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($atividades->hasPages())
            <div class="card-body border-top">
                {{ $atividades->links() }}
            </div>
        @endif
    </div>
@endsection
