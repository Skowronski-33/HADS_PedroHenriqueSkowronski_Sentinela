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
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick="abrirModalRedefinirSenha({{ $user->id }}, {{ json_encode($user->name) }})">
                                    <i class="bi bi-key"></i> Redefinir senha
                                </button>
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

    <div class="modal fade" id="modalRedefinirSenha" tabindex="-1" aria-labelledby="modalRedefinirSenhaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="formRedefinirSenha" action="">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_user_id" id="redefinirSenhaUserId" value="{{ old('_user_id') }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRedefinirSenhaLabel"><i class="bi bi-key"></i> Redefinir Senha</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Defina uma nova senha para <strong id="redefinirSenhaNome"></strong>.</p>

                        <div class="mb-3">
                            <label for="redefinirSenhaPassword" class="form-label">Nova Senha</label>
                            <div class="input-group">
                                <input type="password" name="password" id="redefinirSenhaPassword"
                                       class="form-control @error('password', 'reset_senha') is-invalid @enderror"
                                       minlength="8" required autofocus>
                                <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="redefinirSenhaPassword" tabindex="-1" title="Mostrar senha">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password', 'reset_senha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-1">
                            <label for="redefinirSenhaConfirmacao" class="form-label">Confirmar Senha</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="redefinirSenhaConfirmacao"
                                       class="form-control" minlength="8" required>
                                <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="redefinirSenhaConfirmacao" tabindex="-1" title="Mostrar senha">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="redefinirSenhaFeedback" class="form-text" hidden></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Redefinir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const redefinirSenhaUrlTemplate = @json(route('usuarios.reset-password', ['user' => '__ID__']));

        function abrirModalRedefinirSenha(id, nome) {
            document.getElementById('formRedefinirSenha').action = redefinirSenhaUrlTemplate.replace('__ID__', id);
            document.getElementById('redefinirSenhaUserId').value = id;
            document.getElementById('redefinirSenhaNome').textContent = nome;
            document.getElementById('redefinirSenhaPassword').value = '';
            document.getElementById('redefinirSenhaConfirmacao').value = '';
            document.getElementById('redefinirSenhaConfirmacao').classList.remove('is-invalid', 'is-valid');
            document.getElementById('redefinirSenhaFeedback').hidden = true;

            new bootstrap.Modal(document.getElementById('modalRedefinirSenha')).show();
        }

        document.querySelectorAll('.toggle-senha').forEach((botao) => {
            botao.addEventListener('click', () => {
                const input = document.getElementById(botao.dataset.target);
                const icone = botao.querySelector('i');
                const vaiMostrar = input.type === 'password';

                input.type = vaiMostrar ? 'text' : 'password';
                icone.className = vaiMostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
                botao.title = vaiMostrar ? 'Ocultar senha' : 'Mostrar senha';
            });
        });

        (function () {
            const senhaInput = document.getElementById('redefinirSenhaPassword');
            const confirmInput = document.getElementById('redefinirSenhaConfirmacao');
            const feedback = document.getElementById('redefinirSenhaFeedback');

            function conferirSenhas() {
                if (!confirmInput.value) {
                    feedback.hidden = true;
                    confirmInput.classList.remove('is-invalid', 'is-valid');
                    return;
                }

                const coincide = senhaInput.value === confirmInput.value;

                feedback.hidden = false;
                feedback.textContent = coincide ? 'As senhas coincidem.' : 'As senhas não coincidem.';
                feedback.className = 'form-text ' + (coincide ? 'text-success' : 'text-danger');
                confirmInput.classList.toggle('is-invalid', !coincide);
                confirmInput.classList.toggle('is-valid', coincide);
            }

            senhaInput.addEventListener('input', conferirSenhas);
            confirmInput.addEventListener('input', conferirSenhas);

            confirmInput.closest('form').addEventListener('submit', (e) => {
                if (confirmInput.value && senhaInput.value !== confirmInput.value) {
                    e.preventDefault();
                    conferirSenhas();
                    confirmInput.focus();
                }
            });
        })();
    </script>

    @if ($errors->reset_senha->any())
        @php $usuarioComErro = $users->firstWhere('id', (int) old('_user_id', 0)); @endphp
        <script>
            abrirModalRedefinirSenha(
                {{ old('_user_id', 0) }},
                {{ json_encode($usuarioComErro->name ?? '') }}
            );
        </script>
    @endif
@endsection
