@extends('layouts.app')

@section('title', 'Novo Abrigo')

@section('content')
    <h1 class="page-header-sentinela"><i class="bi bi-house-add-fill"></i> Novo Abrigo</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('abrigos.store') }}">
                @csrf
                @include('abrigos._form', ['abrigo' => null])

                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="{{ route('abrigos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
