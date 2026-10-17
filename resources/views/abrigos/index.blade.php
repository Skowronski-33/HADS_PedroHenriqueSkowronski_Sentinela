@extends('layouts.app')

@section('title', 'Abrigos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Abrigos</h1>
        <a href="{{ route('abrigos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Abrigo
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Endereço</th>
                        <th>Ocupação</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($abrigos as $abrigo)
                        <tr>
                            <td>{{ $abrigo->nome }}</td>
                            <td>{{ $abrigo->endereco }}@if($abrigo->bairro) - {{ $abrigo->bairro }}@endif</td>
                            <td>
                                <span class="badge {{ $abrigo->ocupacao_atual >= $abrigo->capacidade ? 'bg-danger' : 'bg-info' }}">
                                    {{ $abrigo->ocupacao_atual }} / {{ $abrigo->capacidade }}
                                </span>
                            </td>
                            <td>
                                @if (!$abrigo->ativo)
                                    <span class="badge bg-secondary">Inativo</span>
                                @elseif ($abrigo->status === 'Lotado')
                                    <span class="badge bg-warning text-dark">Lotado</span>
                                @else
                                    <span class="badge bg-success">{{ $abrigo->status }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('abrigos.show', $abrigo) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Gerenciar
                                </a>
                                <a href="{{ route('abrigos.edit', $abrigo) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum abrigo cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $abrigos->links() }}
    </div>
@endsection
