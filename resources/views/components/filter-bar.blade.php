{{--
    Barra de filtros "Todos | Ativos | Inativos" em pills + busca com ícone de lupa dentro do input.
    Replica exatamente o padrão da tela de Viaturas (filter-pill-group + search-inline).

    Props:
    - routeName (string, obrigatório)     nome da rota usada tanto nos links de filtro quanto no form de busca
    - search (string|null)                valor atual do campo de busca
    - status (string|null)                filtro atual: null | 'ativos' | 'inativos'
    - searchPlaceholder (string)          placeholder do input de busca
    - searchName (string)                 nome do query param de busca, padrão "busca"
    - statusName (string)                 nome do query param de status, padrão "status"
    - label (string|null)                 texto opcional à esquerda, ex: "Mostrando 9 de 23 viaturas"
--}}
@props([
    'routeName',
    'search' => null,
    'status' => null,
    'searchPlaceholder' => 'Buscar...',
    'searchName' => 'busca',
    'statusName' => 'status',
    'label' => null,
])

<div {{ $attributes->class(['d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3']) }}>
    @if ($label)
        <p class="mb-0 text-muted">{{ $label }}</p>
    @endif

    <div class="filter-pill-group" role="group">
        <button type="button" data-href="{{ route($routeName, [$searchName => $search]) }}"
           class="btn btn-sm {{ ! $status ? 'btn-primary' : 'btn-outline-secondary' }}">Todos</button>
        <button type="button" data-href="{{ route($routeName, [$searchName => $search, $statusName => 'ativos']) }}"
           class="btn btn-sm {{ $status === 'ativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Ativos</button>
        <button type="button" data-href="{{ route($routeName, [$searchName => $search, $statusName => 'inativos']) }}"
           class="btn btn-sm {{ $status === 'inativos' ? 'btn-primary' : 'btn-outline-secondary' }}">Inativos</button>
    </div>

    <form method="GET" action="{{ route($routeName) }}" class="search-inline" style="width: 300px; max-width: 100%;">
        <i class="bi bi-search"></i>
        <input type="text" name="{{ $searchName }}" value="{{ $search }}" class="form-control" placeholder="{{ $searchPlaceholder }}">
        <input type="hidden" name="{{ $statusName }}" value="{{ $status }}">
        @if ($search)
            <a data-href="{{ route($routeName, [$statusName => $status]) }}" class="small d-block mt-1 text-muted" role="link" tabindex="0">Limpar busca</a>
        @endif
    </form>
</div>
