@extends('layouts.app')

@section('title', 'Editar Ocorrência')

@section('content')
    <x-page-header title="Editar Ocorrência" icon="bi-pencil-square" />

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('ocorrencias.update', $ocorrencia) }}">
                @csrf
                @method('PUT')
                @include('ocorrencias._form', ['ocorrencia' => $ocorrencia])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('ocorrencias.show', $ocorrencia) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
