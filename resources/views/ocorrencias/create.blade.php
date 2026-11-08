@extends('layouts.app')

@section('title', 'Nova Ocorrência')

@section('content')
    <x-page-header title="Registrar Ocorrência" icon="bi-fire" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('ocorrencias.store') }}">
                @csrf
                @include('ocorrencias._form', ['ocorrencia' => null])

                <button type="submit" class="btn btn-primary">Registrar</button>
                <a href="{{ route('ocorrencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
