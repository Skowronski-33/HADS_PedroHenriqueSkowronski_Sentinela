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
        <input type="password" name="password" id="password"
               class="form-control @error('password') is-invalid @enderror"
               {{ $isEdit ? '' : 'required' }}>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Confirmar Senha</label>
        <input type="password" name="password_confirmation" id="password_confirmation"
               class="form-control" {{ $isEdit ? '' : 'required' }}>
    </div>
</div>

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
