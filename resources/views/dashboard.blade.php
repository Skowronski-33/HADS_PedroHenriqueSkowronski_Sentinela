@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header-sentinela d-flex justify-content-between align-items-end flex-wrap gap-2">
        <div>
            <h1 class="mb-1">Painel Situacional</h1>
            <p class="text-muted mb-0">
                Bem-vindo, <strong>{{ auth()->user()->name }}</strong>
                @foreach (auth()->user()->getRoleNames() as $role)
                    <span class="badge rounded-pill text-bg-light border ms-1">{{ $role }}</span>
                @endforeach
            </p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            @hasanyrole('Atendente de Ocorrências|Gerente Operacional|Admin')
                <div class="stat-card stat-card-clickable" data-href="{{ route('ocorrencias.index') }}">
            @else
                <div class="stat-card">
            @endhasanyrole
                <div class="stat-card-icon"><i class="bi bi-fire"></i></div>
                <div>
                    <div class="stat-card-label">Ocorrências Abertas</div>
                    <div class="stat-card-value">{{ $ocorrenciasAbertas }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @hasanyrole('Gerenciador de Abrigo|Admin')
                <div class="stat-card stat-card-clickable" data-href="{{ route('abrigos.index') }}">
            @else
                <div class="stat-card">
            @endhasanyrole
                <div class="stat-card-icon"><i class="bi bi-houses-fill"></i></div>
                <div>
                    <div class="stat-card-label">Abrigos Ativos</div>
                    <div class="stat-card-value">{{ $abrigosAtivos }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @hasanyrole('Gerenciador de Abrigo|Admin')
                <div class="stat-card stat-card-clickable" data-href="{{ route('abrigos.index') }}">
            @else
                <div class="stat-card">
            @endhasanyrole
                <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-card-label">Pessoas Abrigadas</div>
                    <div class="stat-card-value">{{ $pessoasAbrigadas }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @hasanyrole('Gerente Operacional|Admin')
                <div class="stat-card stat-card-clickable" data-href="{{ route('viaturas.index') }}">
            @else
                <div class="stat-card">
            @endhasanyrole
                <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="stat-card-label">Viaturas Disponíveis</div>
                    <div class="stat-card-value">{{ $viaturasDisponiveis }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @hasanyrole('Gerente Operacional|Admin')
                <div class="stat-card stat-card-clickable" data-href="{{ route('equipamentos.index') }}">
            @else
                <div class="stat-card">
            @endhasanyrole
                <div class="stat-card-icon"><i class="bi bi-tools"></i></div>
                <div>
                    <div class="stat-card-label">Equipamentos Disponíveis</div>
                    <div class="stat-card-value">{{ $equipamentosDisponiveis }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @hasanyrole('Gerente Operacional|Admin')
                <div class="stat-card stat-card-clickable" data-href="{{ route('voluntarios.index') }}">
            @else
                <div class="stat-card">
            @endhasanyrole
                <div class="stat-card-icon"><i class="bi bi-person-heart"></i></div>
                <div>
                    <div class="stat-card-label">Voluntários Disponíveis</div>
                    <div class="stat-card-value">{{ $voluntariosDisponiveis }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
