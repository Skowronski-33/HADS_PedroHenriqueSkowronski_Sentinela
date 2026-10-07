@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-4">Painel Situacional</h1>

    <div class="alert alert-info">
        Bem-vindo, <strong>{{ auth()->user()->name }}</strong>!
        Seus perfis: 
        @foreach (auth()->user()->getRoleNames() as $role)
            <span class="badge bg-primary">{{ $role }}</span>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Ocorrências Abertas</h5>
                    <p class="display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h5 class="card-title">Viaturas Disponíveis</h5>
                    <p class="display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-bg-info">
                <div class="card-body">
                    <h5 class="card-title">Abrigos Ativos</h5>
                    <p class="display-4">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Em Atendimento</h5>
                    <p class="display-4">0</p>
                </div>
            </div>
        </div>
    </div>
@endsection