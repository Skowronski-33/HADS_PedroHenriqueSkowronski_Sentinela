<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\Viatura;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $tipo = $request->query('tipo');

        $equipamentos = Equipamento::with('tipoEquipamento')
            ->when($busca, fn ($q) => $q->where('nome', 'ilike', "%{$busca}%"))
            ->get()
            ->map(fn (Equipamento $equipamento) => (object) [
                'tipo_recurso' => 'Equipamento',
                'icone' => $equipamento->iconeTipo(),
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
                'icone' => 'bi-truck',
                'nome' => $viatura->identificacao,
                'subtitulo' => $viatura->modelo ?: $viatura->placa,
                'status' => $viatura->status,
                'ativo' => $viatura->ativo,
            ]);

        $recursos = (new Collection())
            ->concat($equipamentos)
            ->concat($viaturas)
            ->when($tipo, fn (Collection $c) => $c->where('tipo_recurso', $tipo))
            ->sortBy('nome')
            ->values();

        return view('recursos.index', [
            'recursos' => $recursos,
            'busca' => $busca,
            'tipo' => $tipo,
        ]);
    }
}
