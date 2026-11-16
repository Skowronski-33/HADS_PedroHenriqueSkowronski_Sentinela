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

    public function update(Request $request, Abrigo $abrigo, PessoaAbrigada $pessoa)
    {
        abort_unless($pessoa->abrigo_id === $abrigo->id, 404);

        $data = $request->validateWithBag('pessoa_edit', [
            'nome' => ['required', 'string', 'max:150'],
            'idade' => ['nullable', 'integer', 'min:0', 'max:120'],
            'contato' => ['nullable', 'string', 'max:20'],
            'necessita_prioridade' => ['nullable', 'boolean'],
            'responsavel_familiar_id' => [
                'nullable',
                Rule::exists('pessoas_abrigadas', 'id')
                    ->where('abrigo_id', $abrigo->id)
                    ->whereNull('data_saida')
                    ->whereNull('responsavel_familiar_id')
                    ->where('id', '!=', $pessoa->id),
            ],
            'observacoes' => ['nullable', 'string'],
        ], [], [
            'responsavel_familiar_id' => 'responsável pela família',
        ]);

        $data['necessita_prioridade'] = $request->boolean('necessita_prioridade');

        // Evita um ciclo familiar: não dá pra virar dependente de quem já é seu dependente.
        if (! empty($data['responsavel_familiar_id']) && $pessoa->dependentes()->where('id', $data['responsavel_familiar_id'])->exists()) {
            return back()->withErrors([
                'responsavel_familiar_id' => 'Essa pessoa já é dependente de quem está editando — não é possível criar um ciclo familiar.',
            ], 'pessoa_edit');
        }

        $pessoa->update($data);

        return redirect()->route('abrigos.show', $abrigo)->with('success', 'Dados atualizados com sucesso.');
    }

    public function saida(Request $request, Abrigo $abrigo, PessoaAbrigada $pessoa)
    {
        abort_unless($pessoa->abrigo_id === $abrigo->id, 404);
        abort_if($pessoa->data_saida !== null, 422, 'Esta pessoa já teve saída registrada.');

        // Mesma regra da transferência: a família sai junto, sem perguntar.
        $responsavel = $pessoa->responsavel_familiar_id ? $pessoa->responsavelFamiliar : $pessoa;

        $grupo = collect([$responsavel])
            ->merge($responsavel->dependentes)
            ->filter(fn ($membro) => $membro->abrigo_id === $abrigo->id && $membro->data_saida === null)
            ->unique('id')
            ->values();

        $agora = now();

        DB::transaction(function () use ($grupo, $agora) {
            foreach ($grupo as $membro) {
                $membro->update(['data_saida' => $agora]);
            }
        });

        $mensagem = $grupo->count() > 1
            ? "Saída registrada com sucesso para a família ({$grupo->count()} pessoas, incluindo dependentes)."
            : 'Saída registrada com sucesso.';

        return redirect()->route('abrigos.show', $abrigo)->with('success', $mensagem);
    }

    public function transferir(Request $request, Abrigo $abrigo, PessoaAbrigada $pessoa)
    {
        abort_unless($pessoa->abrigo_id === $abrigo->id, 404);
        abort_if($pessoa->data_saida !== null, 422, 'Esta pessoa já teve saída registrada.');

        $data = $request->validateWithBag('pessoa_transferir', [
            'abrigo_destino_id' => ['required', 'integer', 'exists:abrigos,id'],
            'incluir_familia' => ['nullable', 'boolean'],
            'observacoes' => ['nullable', 'string'],
        ], [], ['abrigo_destino_id' => 'abrigo de destino']);

        if ((int) $data['abrigo_destino_id'] === $abrigo->id) {
            return redirect()->route('abrigos.show', $abrigo)
                ->with('error', 'O abrigo de destino deve ser diferente do abrigo atual.');
        }

        $destino = Abrigo::findOrFail($data['abrigo_destino_id']);

        // Por padrão a família inteira é transferida junto (se a pessoa clicada for o
        // responsável, os dependentes vão junto; se for dependente, o responsável e os
        // demais dependentes também vão) — mas quem está transferindo pode optar por
        // levar só a pessoa selecionada, deixando o restante da família no abrigo atual.
        $incluirFamilia = $request->boolean('incluir_familia', true);
        $responsavel = $pessoa->responsavel_familiar_id ? $pessoa->responsavelFamiliar : $pessoa;

        $grupo = $incluirFamilia
            ? collect([$responsavel])
                ->merge($responsavel->dependentes)
                ->filter(fn ($membro) => $membro->abrigo_id === $abrigo->id && $membro->data_saida === null)
                ->unique('id')
                ->values()
            : collect([$pessoa]);

        DB::transaction(function () use ($grupo, $abrigo, $destino, $data, $request, $incluirFamilia, $pessoa) {
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

            if (! $incluirFamilia) {
                // A pessoa deixou a família para trás (ou foi deixada por ela): o vínculo
                // familiar não faz mais sentido com quem ficou em outro abrigo.
                $pessoa->update(['responsavel_familiar_id' => null]);
                $pessoa->dependentes()->update(['responsavel_familiar_id' => null]);
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
