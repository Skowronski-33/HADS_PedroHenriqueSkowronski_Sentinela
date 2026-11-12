@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
    <x-page-header title="Usuários" icon="bi-people-fill" action-route="usuarios.create" action-text="Novo Usuário" />

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <x-status-badge :text="$user->ativo ? 'Ativo' : 'Inativo'" :color="$user->ativo ? 'disponivel' : 'indisponivel'" />
                            </td>
                            <td class="text-end">
                                <button type="button" data-href="{{ route('usuarios.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form action="{{ route('usuarios.reset-password', $user) }}" method="POST" class="d-inline"
                                      data-confirm="Redefinir a senha de {{ $user->name }}? Uma nova senha temporária será gerada.">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-key"></i> Redefinir senha
                                    </button>
                                </form>
                                <form action="{{ route('usuarios.toggle-ativo', $user) }}" method="POST" class="d-inline"
                                      @if ($user->ativo) data-confirm="Inativar este usuário? Ele não poderá mais acessar o sistema." @endif>
                                    @csrf
                                    @method('PATCH')
                                    @if ($user->ativo)
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-person-dash"></i> Inativar
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-person-check"></i> Reativar
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>

    <div class="modal fade" id="modalSenhaTemporaria" tabindex="-1" aria-labelledby="modalSenhaTemporariaLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSenhaTemporariaLabel"><i class="bi bi-key"></i> Senha redefinida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        Nova senha temporária de <strong>{{ session('usuario_senha_resetada') }}</strong>.
                        Anote e informe ao usuário — ela não será mostrada novamente.
                    </p>
                    <div class="input-group">
                        <input type="text" id="senhaTemporariaInput" class="form-control fw-bold" value="{{ session('senha_temporaria') }}" readonly>
                        <button type="button" class="btn btn-outline-secondary" id="copiarSenhaTemporaria">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendi</button>
                </div>
            </div>
        </div>
    </div>

    @if (session('senha_temporaria'))
        <script>
            new bootstrap.Modal(document.getElementById('modalSenhaTemporaria')).show();

            document.getElementById('copiarSenhaTemporaria').addEventListener('click', function () {
                const input = document.getElementById('senhaTemporariaInput');
                input.select();
                navigator.clipboard?.writeText(input.value);
            });
        </script>
    @endif
@endsection
