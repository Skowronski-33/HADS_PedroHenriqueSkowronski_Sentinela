<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\Viatura;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecursoController extends Controller
{
    private const ORDENAR_OPCOES = ['nome', 'tipo', 'status'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $tipo = $request->query('tipo');
        $ordenar = $request->query('ordenar', 'nome');

        if (! in_array($ordenar, self::ORDENAR_OPCOES, true)) {
            $ordenar = 'nome';
        }

        $equipamentos = Equipamento::with('tipoEquipamento')
            ->when($busca, fn ($q) => $q->where('nome', 'ilike', "%{$busca}%"))
            ->get()
            ->map(fn (Equipamento $equipamento) => (object) [
                'tipo_recurso' => 'Equipamento',
                'nome' => $equipamento->nome,
                'subtitulo' => $equipamento->tipoEquipamento->nome,
                'status' => $equipamento->status,
                'ativo' => $equipamento->ativo,
            ]);

        $viaturas = Viatura::when($busca, fn ($q) => $q->where('identificacao', 'ilike', "%{$busca}%")
                ->orWhere('placa', 'ilike', "%{$busca}%"))
            ->get()
            ->map(fn (Viatura $viatura) => (object) [
                'tipo_recurso' => 'Viatura',
                'nome' => $viatura->identificacao,
                'subtitulo' => $viatura->modelo ?: $viatura->placa,
                'status' => $viatura->status,
                'ativo' => $viatura->ativo,
            ]);

        $criterioOrdenacao = match ($ordenar) {
            'tipo' => [['tipo_recurso', 'asc'], ['nome', 'asc']],
            'status' => [['status', 'asc'], ['nome', 'asc']],
            default => [['nome', 'asc']],
        };

        $recursos = (new Collection())
            ->concat($equipamentos)
            ->concat($viaturas)
            ->when($tipo, fn (Collection $c) => $c->where('tipo_recurso', $tipo))
            ->sortBy($criterioOrdenacao)
            ->values();

        return view('recursos.index', [
            'recursos' => $recursos,
            'busca' => $busca,
            'tipo' => $tipo,
            'ordenar' => $ordenar,
        ]);
    }
}
