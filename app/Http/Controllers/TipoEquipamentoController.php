<?php

namespace App\Http\Controllers;

use App\Models\TipoEquipamento;
use Illuminate\Http\Request;

class TipoEquipamentoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validateWithBag('novo_tipo', [
            'nome' => ['required', 'string', 'max:100'],
        ]);

        $nome = trim($data['nome']);

        $jaExiste = TipoEquipamento::whereRaw('lower(nome) = ?', [mb_strtolower($nome)])->exists();

        if ($jaExiste) {
            return redirect()->route('equipamentos.index')
                ->with('error', "Já existe um tipo de equipamento chamado \"{$nome}\".")
                ->with('reabrir_modal_tipos', true);
        }

        TipoEquipamento::create(['nome' => $nome, 'ativo' => true]);

        return redirect()->route('equipamentos.index')->with('success', "Tipo \"{$nome}\" cadastrado com sucesso.");
    }

    public function update(Request $request, TipoEquipamento $tipo)
    {
        $data = $request->validateWithBag('tipo', [
            'nome' => ['required', 'string', 'max:100'],
        ]);

        // Unicidade sem diferenciar maiúsculas/minúsculas, igual ao critério usado ao criar um tipo novo.
        $jaExiste = TipoEquipamento::whereRaw('lower(nome) = ?', [mb_strtolower(trim($data['nome']))])
            ->where('id', '!=', $tipo->id)
            ->exists();

        if ($jaExiste) {
            return redirect()->route('equipamentos.index')
                ->with('error', "Já existe um tipo de equipamento chamado \"{$data['nome']}\".")
                ->with('reabrir_modal_tipos', true);
        }

        $tipo->update(['nome' => trim($data['nome'])]);

        return redirect()->route('equipamentos.index')->with('success', 'Tipo de equipamento renomeado com sucesso.');
    }

    public function destroy(TipoEquipamento $tipo)
    {
        $emUso = $tipo->equipamentos()->count();

        if ($emUso > 0) {
            return redirect()->route('equipamentos.index')
                ->with('error', "Não é possível excluir \"{$tipo->nome}\": ainda há {$emUso} equipamento(s) cadastrado(s) com esse tipo.")
                ->with('reabrir_modal_tipos', true);
        }

        $tipo->delete();

        return redirect()->route('equipamentos.index')->with('success', 'Tipo de equipamento excluído com sucesso.');
    }
}
