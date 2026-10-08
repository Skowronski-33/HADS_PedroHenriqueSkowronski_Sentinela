@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
    <h1 class="mb-4">Editar Usuário</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.update', $user) }}">
                @csrf
                @method('PUT')
                @include('usuarios._form', ['roles' => $roles, 'user' => $user, 'perfilAtual' => $perfilAtual])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
