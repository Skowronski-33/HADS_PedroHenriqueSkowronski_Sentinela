{{--
    Card unificado para recursos (Viaturas, Equipamentos, Abrigos, Voluntários...).
    Mesmo visual da tela de Viaturas: borda lateral colorida por status, ícone + título + subtítulo
    em chip, menu "⋮" opcional, e rodapé com badge de status (ou conteúdo customizado).

    Props:
    - title (string, obrigatório)
    - subtitle (string|null)        ex: placa da viatura, patrimônio do equipamento, habilidade do voluntário
    - subtitleColor (string|null)   cor extra do chip do subtítulo (slate|red|blue|teal|purple|amber|green,
                                     ver tag-chip-* no sentinela.css); omitir para o chip neutro padrão
    - icon (string|null)            classe do Bootstrap Icons; se omitido, o card não exibe o quadro de ícone
                                     (ex: Voluntário, que não tem imagem/avatar)
    - borderColor (string)          slug conhecido (disponivel|uso|atendimento|manutencao|indisponivel)
                                     ou qualquer cor Bootstrap (ex: "danger", "success") via var(--bs-*)
    - statusText (string|null)      texto do badge padrão do rodapé (ignorado se o slot $footer for usado)
    - statusColor (string)          cor do badge padrão do rodapé (mesmas regras do x-status-badge)

    Slots nomeados:
    - $slot (padrão)      conteúdo do meio do card (ex: .equip-card-meta com detalhes, contexto de atendimento)
    - $actions            itens <li> do menu "⋮" (dropdown). Se omitido, o menu não é renderizado.
    - $headerBadge        badge extra no canto superior direito, à esquerda do $topBadge/menu "⋮"
                           (ex: status "Ativo" do abrigo, quando o rodapé é usado para a ação primária)
    - $topBadge           conteúdo extra no topo direito, à esquerda do menu "⋮" (ex: badge de quantidade)
    - $extraContent        conteúdo extra entre o meio e o rodapé (ex: barra de progresso de ocupação do abrigo)
    - $footer              substitui o badge padrão do rodapé por conteúdo customizado
                           (ex: o <select> de status pill-select de Viaturas/Equipamentos, ou um botão
                           de ação primária com .stretched-link — ver Abrigos)

    Dica: para tornar o card inteiro clicável, dê a classe "stretched-link" a um link/botão dentro do
    card (ex: no $footer). O componente já garante position:relative no card e eleva o z-index do menu
    "⋮" para que ele continue clicável por cima do link esticado.
--}}
@props([
    'title',
    'subtitle' => null,
    'subtitleColor' => null,
    'icon' => null,
    'borderColor' => 'indisponivel',
    'statusText' => null,
    'statusColor' => 'indisponivel',
])

@php
    $slugsConhecidos = ['disponivel', 'uso', 'atendimento', 'manutencao', 'indisponivel'];
    $temClasseDeBorda = in_array($borderColor, $slugsConhecidos, true);
    $estiloDeBorda = $temClasseDeBorda ? null : "border-left: 4px solid var(--bs-{$borderColor}, #6c7a8c);";
@endphp

<div {{ $attributes->class(['equip-card', "equip-card-accent-{$borderColor}" => $temClasseDeBorda]) }}
     @if ($estiloDeBorda) style="{{ $estiloDeBorda }}" @endif>
    <div class="equip-card-top">
        <div class="d-flex gap-2 min-w-0">
            @if ($icon)
                <div class="equip-card-icon"><i class="bi {{ $icon }}"></i></div>
            @endif
            <div class="equip-card-heading">
                <p class="equip-card-name">{{ $title }}</p>
                @if ($subtitle)
                    <span class="tag-chip-subtle {{ $subtitleColor ? "tag-chip-{$subtitleColor}" : '' }}">{{ $subtitle }}</span>
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            {{ $headerBadge ?? '' }}
            {{ $topBadge ?? '' }}

            @isset($actions)
                <div class="dropdown">
                    <button type="button" class="equip-card-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Mais ações">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        {{ $actions }}
                    </ul>
                </div>
            @endisset
        </div>
    </div>

    {{ $slot }}

    {{ $extraContent ?? '' }}

    <div class="equip-card-foot justify-content-end">
        @isset($footer)
            {{ $footer }}
        @elseif ($statusText)
            <x-status-badge :text="$statusText" :color="$statusColor" />
        @endif
    </div>
</div>
