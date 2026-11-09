<?php

namespace App\Http\Controllers;

use App\Models\TipoOcorrencia;
use Illuminate\Http\Request;

class TipoOcorrenciaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validateWithBag('novo_tipo_ocorrencia', [
            'nome' => ['required', 'string', 'max:100'],
        ]);

        $nome = trim($data['nome']);

        $jaExiste = TipoOcorrencia::whereRaw('lower(nome) = ?', [mb_strtolower($nome)])->exists();

        if ($jaExiste) {
            return redirect()->route('ocorrencias.index')
                ->with('error', "Já existe um tipo de ocorrência chamado \"{$nome}\".")
                ->with('reabrir_modal_tipos_ocorrencia', true);
        }

        TipoOcorrencia::create(['nome' => $nome, 'ativo' => true]);

        return redirect()->route('ocorrencias.index')->with('success', "Tipo \"{$nome}\" cadastrado com sucesso.");
    }

    public function update(Request $request, TipoOcorrencia $tipo)
    {
        $data = $request->validateWithBag('tipo_ocorrencia', [
            'nome' => ['required', 'string', 'max:100'],
        ]);

        $jaExiste = TipoOcorrencia::whereRaw('lower(nome) = ?', [mb_strtolower(trim($data['nome']))])
            ->where('id', '!=', $tipo->id)
            ->exists();

        if ($jaExiste) {
            return redirect()->route('ocorrencias.index')
                ->with('error', "Já existe um tipo de ocorrência chamado \"{$data['nome']}\".")
                ->with('reabrir_modal_tipos_ocorrencia', true);
        }

        $tipo->update(['nome' => trim($data['nome'])]);

        return redirect()->route('ocorrencias.index')->with('success', 'Tipo de ocorrência renomeado com sucesso.');
    }

    public function destroy(TipoOcorrencia $tipo)
    {
        $emUso = $tipo->ocorrencias()->count();

        if ($emUso > 0) {
            return redirect()->route('ocorrencias.index')
                ->with('error', "Não é possível excluir \"{$tipo->nome}\": ainda há {$emUso} ocorrência(s) cadastrada(s) com esse tipo.")
                ->with('reabrir_modal_tipos_ocorrencia', true);
        }

        $tipo->delete();

        return redirect()->route('ocorrencias.index')->with('success', 'Tipo de ocorrência excluído com sucesso.');
    }
}
