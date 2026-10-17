<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use App\Models\Donativo;
use App\Models\MovimentacaoDonativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonativoController extends Controller
{
    public function store(Request $request, Abrigo $abrigo)
    {
        $data = $request->validate([
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:200'],
            'unidade_medida' => ['required', 'string', 'max:30'],
        ]);

        $abrigo->donativos()->create($data + ['estoque_atual' => 0, 'ativo' => true]);

        return redirect()->route('abrigos.show', $abrigo)->with('success', 'Item de donativo cadastrado com sucesso.');
    }

    public function entrada(Request $request, Abrigo $abrigo, Donativo $donativo)
    {
        $data = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $abrigo, $donativo, $request) {
            $donativo->increment('estoque_atual', $data['quantidade']);

            MovimentacaoDonativo::create([
                'donativo_id' => $donativo->id,
                'user_id' => $request->user()->id,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $data['quantidade'],
                'data_hora' => now(),
                'observacoes' => $data['observacoes'] ?? null,
            ]);
        });

        return redirect()->route('abrigos.show', $abrigo)->with('success', 'Entrada de donativo registrada com sucesso.');
    }

    public function saida(Request $request, Abrigo $abrigo, Donativo $donativo)
    {
        $data = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ]);

        if ($data['quantidade'] > $donativo->estoque_atual) {
            return redirect()->route('abrigos.show', $abrigo)
                ->with('error', "Saída não permitida: estoque atual de \"{$donativo->tipo}\" é {$donativo->estoque_atual} {$donativo->unidade_medida}.");
        }

        DB::transaction(function () use ($data, $abrigo, $donativo, $request) {
            $donativo->decrement('estoque_atual', $data['quantidade']);

            MovimentacaoDonativo::create([
                'donativo_id' => $donativo->id,
                'user_id' => $request->user()->id,
                'tipo_movimentacao' => 'saida',
                'quantidade' => $data['quantidade'],
                'data_hora' => now(),
                'observacoes' => $data['observacoes'] ?? null,
            ]);
        });

        return redirect()->route('abrigos.show', $abrigo)->with('success', 'Saída de donativo registrada com sucesso.');
    }
}
