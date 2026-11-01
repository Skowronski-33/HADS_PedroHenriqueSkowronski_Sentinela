@extends('layouts.app')

@section('title', 'Editar Equipe')

@section('content')
    <h1 class="page-header-sentinela"><i class="bi bi-pencil-square"></i> Editar Equipe</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('equipes.update', $equipe) }}">
                @csrf
                @method('PUT')
                @include('equipes._form', ['equipe' => $equipe])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('equipes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
