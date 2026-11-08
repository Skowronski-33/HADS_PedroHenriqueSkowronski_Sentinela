{{--
    Cabeçalho padrão de tela (listagem, detalhe, criação/edição).
    Ícone + título grande à esquerda, botão de ação primário (laranja) à direita.

    Props:
    - title (string, obrigatório)       também pode ser passado como <x-slot:title> para conteúdo rico (ex: badges no cabeçalho)
    - icon (string|null)                classe do Bootstrap Icons, ex: "bi-truck"
    - subtitle (string|null)            linha secundária sob o título; também aceita <x-slot:subtitle> para conteúdo rico
    - actionRoute (string|null)         nome da rota do botão de ação (link <a>)
    - actionParams (mixed)              parâmetro(s) da rota, ex: $abrigo — repassado para route($actionRoute, $actionParams)
    - modalTarget (string|null)         id do modal a abrir (botão <button>), em vez de navegar — use um dos dois
    - actionText (string|null)          texto do botão (obrigatório se actionRoute OU modalTarget for informado)
    - actionIcon (string)               ícone do botão de ação, padrão "bi-plus-lg"
    - actionVariant (string)            variante Bootstrap do botão, padrão "primary"

    Slot nomeado:
    - $actions   botão(ões) extra, renderizados antes do botão de ação principal
                 (ex: "Gerenciar Tipos", ou — em telas de detalhe sem uma única "ação primária" —
                 todos os botões de ação, deixando actionRoute/modalTarget de fora)
--}}
@props([
    'title',
    'icon' => null,
    'subtitle' => null,
    'actionRoute' => null,
    'actionParams' => [],
    'modalTarget' => null,
    'actionText' => null,
    'actionIcon' => 'bi-plus-lg',
    'actionVariant' => 'primary',
])

<div {{ $attributes->class(['page-header-sentinela d-flex justify-content-between align-items-center flex-wrap gap-2']) }}>
    <div>
        <h1 class="mb-0">
            @if ($icon)<i class="bi {{ $icon }}"></i>@endif
            {{ $title }}
        </h1>
        @if ($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>

    @if (isset($actions) || $modalTarget || $actionRoute)
        <div class="d-flex gap-2 flex-wrap">
            {{ $actions ?? '' }}

            @if ($modalTarget)
                <button type="button" class="btn btn-{{ $actionVariant }} btn-pill" data-bs-toggle="modal" data-bs-target="#{{ $modalTarget }}">
                    <i class="bi {{ $actionIcon }}"></i> {{ $actionText }}
                </button>
            @elseif ($actionRoute)
                <a href="{{ route($actionRoute, $actionParams) }}" class="btn btn-{{ $actionVariant }} btn-pill">
                    <i class="bi {{ $actionIcon }}"></i> {{ $actionText }}
                </a>
            @endif
        </div>
    @endif
</div>
