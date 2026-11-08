@extends('layouts.app')

@section('title', 'Nova Equipe')

@section('content')
    <x-page-header title="Nova Equipe" icon="bi-people-fill" />

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
