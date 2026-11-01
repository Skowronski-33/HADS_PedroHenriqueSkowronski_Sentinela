@extends('layouts.app')

@section('title', 'Editar Ocorrência')

@section('content')
    <h1 class="page-header-sentinela"><i class="bi bi-pencil-square"></i> Editar Ocorrência</h1>

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
