{{--
    Etiqueta de status em formato de pílula.

    Props:
    - text (string, obrigatório)
    - color (string)   duas formas aceitas:
                        1) um "slug" conhecido do design system: disponivel | uso | atendimento | manutencao | indisponivel
                           -> usa .status-badge-{slug} (cor sólida, texto branco, igual à pílula de Viaturas/Equipamentos)
                        2) qualquer cor Bootstrap, ex: "bg-success", "bg-danger", "bg-warning"
                           -> vira uma pílula Bootstrap (badge rounded-pill), para status que não têm slug definido
                           (ex: prioridade de ocorrência, "Lotado" de abrigo)
    - icon (string|null)   classe do Bootstrap Icons, opcional
--}}
@props([
    'text',
    'color' => 'indisponivel',
    'icon' => null,
])

@php
    $slugsConhecidos = ['disponivel', 'uso', 'atendimento', 'manutencao', 'indisponivel'];
    $slug = str_starts_with($color, 'bg-') ? substr($color, 3) : $color;
    $usaSlug = in_array($slug, $slugsConhecidos, true);
    $precisaTextoEscuro = in_array($color, ['bg-warning', 'bg-light', 'bg-info'], true);
@endphp

@if ($usaSlug)
    <span {{ $attributes->class(["status-badge status-badge-{$slug}"]) }}>
        @if ($icon)<i class="bi {{ $icon }}"></i>@endif {{ $text }}
    </span>
@else
    <span {{ $attributes->class(["badge rounded-pill {$color}", 'text-dark' => $precisaTextoEscuro]) }}>
        @if ($icon)<i class="bi {{ $icon }}"></i>@endif {{ $text }}
    </span>
@endif
