@extends('layouts.app')

@section('title', 'Editar Abrigo')

@section('content')
    <h1 class="mb-4">Editar Abrigo</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('abrigos.update', $abrigo) }}">
                @csrf
                @method('PUT')
                @include('abrigos._form', ['abrigo' => $abrigo])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('abrigos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
