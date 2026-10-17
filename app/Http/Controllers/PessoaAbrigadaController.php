<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use App\Models\PessoaAbrigada;
use App\Models\PessoaAbrigadaTransferencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PessoaAbrigadaController extends Controller
{
    public function store(Request $request, Abrigo $abrigo)
    {
        $data = $request->validateWithBag('pessoa', [
            'nome' => ['required', 'string', 'max:150'],
            'idade' => ['nullable', 'integer', 'min:0', 'max:120'],
            'contato' => ['nullable', 'string', 'max:20'],
            'necessita_prioridade' => ['nullable', 'boolean'],
            'responsavel_familiar_id' => [
                'nullable',
                Rule::exists('pessoas_abrigadas', 'id')
                    ->where('abrigo_id', $abrigo->id)
                    ->whereNull('data_saida')
                    ->whereNull('responsavel_familiar_id'),
            ],
            'observacoes' => ['nullable', 'string'],
        ], [], [
            'responsavel_familiar_id' => 'responsável pela família',
        ]);

        $data['necessita_prioridade'] = $request->boolean('necessita_prioridade');

        $abrigo->pessoasAbrigadas()->create($data + [
            'data_entrada' => now(),
            'registrado_por' => $request->user()->id,
        ]);

        if ($abrigo->ocupacaoAtual() >= $abrigo->capacidade) {
            return redirect()->route('abrigos.show', $abrigo)
                ->with('error', "Atenção: o abrigo \"{$abrigo->nome}\" atingiu ou ultrapassou a capacidade máxima ({$abrigo->ocupacaoAtual()}/{$abrigo->capacidade}).");
        }

        return redirect()->route('abrigos.show', $abrigo)
            ->with('success', 'Pessoa cadastrada com sucesso, entrada registrada em '.now()->format('d/m/Y H:i').'.');
    }

    public function saida(Request $request, Abrigo $abrigo, PessoaAbrigada $pessoa)
    {
        abort_unless($pessoa->abrigo_id === $abrigo->id, 404);
        abort_if($pessoa->data_saida !== null, 422, 'Esta pessoa já teve saída registrada.');

        $pessoa->update(['data_saida' => now()]);

        return redirect()->route('abrigos.show', $abrigo)->with('success', 'Saída registrada com sucesso.');
    }

    public function transferir(Request $request, Abrigo $abrigo, PessoaAbrigada $pessoa)
    {
        abort_unless($pessoa->abrigo_id === $abrigo->id, 404);
        abort_if($pessoa->data_saida !== null, 422, 'Esta pessoa já teve saída registrada.');

        $data = $request->validate([
            'abrigo_destino_id' => ['required', 'integer', 'exists:abrigos,id'],
            'observacoes' => ['nullable', 'string'],
        ], [], ['abrigo_destino_id' => 'abrigo de destino']);

        if ((int) $data['abrigo_destino_id'] === $abrigo->id) {
            return redirect()->route('abrigos.show', $abrigo)
                ->with('error', 'O abrigo de destino deve ser diferente do abrigo atual.');
        }

        $destino = Abrigo::findOrFail($data['abrigo_destino_id']);

        DB::transaction(function () use ($pessoa, $abrigo, $destino, $data, $request) {
            PessoaAbrigadaTransferencia::create([
                'pessoa_abrigada_id' => $pessoa->id,
                'abrigo_origem_id' => $abrigo->id,
                'abrigo_destino_id' => $destino->id,
                'user_id' => $request->user()->id,
                'data_hora' => now(),
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            $pessoa->update([
                'abrigo_id' => $destino->id,
                'data_entrada' => now(),
            ]);
        });

        if ($destino->ocupacaoAtual() >= $destino->capacidade) {
            return redirect()->route('abrigos.show', $abrigo)
                ->with('error', "Pessoa transferida, mas o abrigo de destino \"{$destino->nome}\" atingiu ou ultrapassou a capacidade máxima ({$destino->ocupacaoAtual()}/{$destino->capacidade}).");
        }

        return redirect()->route('abrigos.show', $abrigo)
            ->with('success', "Pessoa transferida para \"{$destino->nome}\" com sucesso.");
    }
}
