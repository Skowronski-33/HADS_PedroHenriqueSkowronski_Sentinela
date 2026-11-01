@extends('layouts.app')

@section('title', 'Nova Equipe')

@section('content')
    <h1 class="page-header-sentinela"><i class="bi bi-people-fill"></i> Nova Equipe</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('equipes.store') }}">
                @csrf
                @include('equipes._form', ['equipe' => null])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('equipes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
