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

        // A família é transferida inteira, sem perguntar: se a pessoa clicada for o
        // responsável, os dependentes vão junto; se for dependente, o responsável e os
        // demais dependentes também vão — ninguém fica separado da própria família.
        $responsavel = $pessoa->responsavel_familiar_id ? $pessoa->responsavelFamiliar : $pessoa;

        $grupo = collect([$responsavel])
            ->merge($responsavel->dependentes)
            ->filter(fn ($membro) => $membro->abrigo_id === $abrigo->id && $membro->data_saida === null)
            ->unique('id')
            ->values();

        DB::transaction(function () use ($grupo, $abrigo, $destino, $data, $request) {
            foreach ($grupo as $membro) {
                PessoaAbrigadaTransferencia::create([
                    'pessoa_abrigada_id' => $membro->id,
                    'abrigo_origem_id' => $abrigo->id,
                    'abrigo_destino_id' => $destino->id,
                    'user_id' => $request->user()->id,
                    'data_hora' => now(),
                    'observacoes' => $data['observacoes'] ?? null,
                ]);

                $membro->update([
                    'abrigo_id' => $destino->id,
                    'data_entrada' => now(),
                ]);
            }
        });

        $mensagem = $grupo->count() > 1
            ? "Família transferida para \"{$destino->nome}\" com sucesso ({$grupo->count()} pessoas, incluindo dependentes)."
            : "Pessoa transferida para \"{$destino->nome}\" com sucesso.";

        if ($destino->ocupacaoAtual() >= $destino->capacidade) {
            return redirect()->route('abrigos.show', $abrigo)
                ->with('error', "{$mensagem} Atenção: o abrigo de destino atingiu ou ultrapassou a capacidade máxima ({$destino->ocupacaoAtual()}/{$destino->capacidade}).");
        }

        return redirect()->route('abrigos.show', $abrigo)->with('success', $mensagem);
    }
}
