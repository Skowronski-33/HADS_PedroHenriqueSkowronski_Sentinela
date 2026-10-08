@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')
    <h1 class="mb-4">Novo Usuário</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                @include('usuarios._form', ['roles' => $roles, 'user' => null, 'perfilAtual' => null])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
