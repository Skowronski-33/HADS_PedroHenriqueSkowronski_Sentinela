@extends('layouts.app')

@section('title', 'Novo Abrigo')

@section('content')
    <x-page-header title="Novo Abrigo" icon="bi-house-add-fill" />

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
