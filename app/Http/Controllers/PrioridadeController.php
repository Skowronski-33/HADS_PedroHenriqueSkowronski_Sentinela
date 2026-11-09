<?php

namespace App\Http\Controllers;

use App\Models\Prioridade;
use Illuminate\Http\Request;

class PrioridadeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validateWithBag('nova_prioridade', [
            'nome' => ['required', 'string', 'max:100'],
        ]);

        $nome = trim($data['nome']);

        $jaExiste = Prioridade::whereRaw('lower(nome) = ?', [mb_strtolower($nome)])->exists();

        if ($jaExiste) {
            return redirect()->route('ocorrencias.index')
                ->with('error', "Já existe uma prioridade chamada \"{$nome}\".")
                ->with('reabrir_modal_prioridades', true);
        }

        Prioridade::create(['nome' => $nome, 'ativo' => true]);

        return redirect()->route('ocorrencias.index')->with('success', "Prioridade \"{$nome}\" cadastrada com sucesso.");
    }

    public function update(Request $request, Prioridade $prioridade)
    {
        $data = $request->validateWithBag('prioridade', [
            'nome' => ['required', 'string', 'max:100'],
        ]);

        $jaExiste = Prioridade::whereRaw('lower(nome) = ?', [mb_strtolower(trim($data['nome']))])
            ->where('id', '!=', $prioridade->id)
            ->exists();

        if ($jaExiste) {
            return redirect()->route('ocorrencias.index')
                ->with('error', "Já existe uma prioridade chamada \"{$data['nome']}\".")
                ->with('reabrir_modal_prioridades', true);
        }

        $prioridade->update(['nome' => trim($data['nome'])]);

        return redirect()->route('ocorrencias.index')->with('success', 'Prioridade renomeada com sucesso.');
    }

    public function destroy(Prioridade $prioridade)
    {
        $emUso = $prioridade->ocorrencias()->count();

        if ($emUso > 0) {
            return redirect()->route('ocorrencias.index')
                ->with('error', "Não é possível excluir \"{$prioridade->nome}\": ainda há {$emUso} ocorrência(s) com essa prioridade.")
                ->with('reabrir_modal_prioridades', true);
        }

        $prioridade->delete();

        return redirect()->route('ocorrencias.index')->with('success', 'Prioridade excluída com sucesso.');
    }
}
