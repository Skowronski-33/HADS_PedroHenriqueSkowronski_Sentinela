@php $isEdit = $user !== null; @endphp

<div class="mb-3">
    <label for="name" class="form-label">Nome</label>
    <input type="text" name="name" id="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $user->name ?? '') }}" required autofocus>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">E-mail</label>
    <input type="email" name="email" id="email"
           class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">
            Senha @if ($isEdit) <span class="text-muted">(deixe em branco para manter a atual)</span> @endif
        </label>
        <div class="input-group">
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   {{ $isEdit ? '' : 'required' }}>
            <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="password" tabindex="-1" title="Mostrar senha">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Confirmar Senha</label>
        <div class="input-group">
            <input type="password" name="password_confirmation" id="password_confirmation"
                   class="form-control" {{ $isEdit ? '' : 'required' }}>
            <button type="button" class="btn btn-outline-secondary toggle-senha" data-target="password_confirmation" tabindex="-1" title="Mostrar senha">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <div id="senhaFeedback" class="form-text" hidden></div>
    </div>
</div>

<script>
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
        const senhaInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const feedback = document.getElementById('senhaFeedback');

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

<div class="mb-3">
    <label for="perfil" class="form-label">Perfil de Acesso</label>
    <select name="perfil" id="perfil" class="form-select @error('perfil') is-invalid @enderror" required>
        <option value="">Selecione...</option>
        @foreach ($roles as $role)
            <option value="{{ $role }}" {{ old('perfil', $perfilAtual) === $role ? 'selected' : '' }}>
                {{ $role }}
            </option>
        @endforeach
    </select>
    @error('perfil')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
