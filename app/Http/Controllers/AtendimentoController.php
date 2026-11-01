<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Equipe;
use App\Models\Ocorrencia;
use App\Models\Viatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AtendimentoController extends Controller
{
    public const STATUS_OPCOES = ['Em Deslocamento', 'No Local', 'Concluído'];

    public function store(Request $request, Ocorrencia $ocorrencia)
    {
        if (in_array($ocorrencia->status, ['Encerrada', 'Cancelada'])) {
            return back()->with('error', 'Não é possível despachar um atendimento para uma ocorrência encerrada ou cancelada.');
        }

        $data = $request->validate([
            'equipe_id' => [
                'required',
                Rule::exists('equipes', 'id')->where('ativo', true)->where('status', 'Disponível'),
            ],
            'viatura_id' => [
                'nullable',
                Rule::exists('viaturas', 'id')->where('ativo', true)->where('status', 'Disponível'),
            ],
            'equipamentos' => ['nullable', 'array'],
            'equipamentos.*' => ['exists:equipamentos,id'],
            'quantidades' => ['nullable', 'array'],
            'quantidades.*' => ['integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ], [], ['equipe_id' => 'equipe', 'viatura_id' => 'viatura']);

        DB::transaction(function () use ($data, $ocorrencia) {
            $atendimento = $ocorrencia->atendimentos()->create([
                'equipe_id' => $data['equipe_id'],
                'viatura_id' => $data['viatura_id'] ?? null,
                'data_hora_saida' => now(),
                'status' => 'Em Deslocamento',
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            if (! empty($data['equipamentos'])) {
                $quantidades = $data['quantidades'] ?? [];

                $atendimento->equipamentos()->sync(
                    collect($data['equipamentos'])->mapWithKeys(fn ($id) => [
                        $id => ['quantidade_utilizada' => $quantidades[$id] ?? 1],
                    ])
                );
            }

            if ($ocorrencia->status === 'Aberta') {
                $ocorrencia->update(['status' => 'Em Atendimento']);
            }

            Equipe::where('id', $data['equipe_id'])->update(['status' => 'Em Serviço']);

            if (! empty($data['viatura_id'])) {
                Viatura::where('id', $data['viatura_id'])->update(['status' => 'Em Atendimento']);
            }
        });

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Atendimento despachado com sucesso.');
    }

    public function status(Request $request, Ocorrencia $ocorrencia, Atendimento $atendimento)
    {
        abort_unless($atendimento->ocorrencia_id === $ocorrencia->id, 404);

        if ($atendimento->status === 'Concluído') {
            return back()->with('error', 'Este atendimento já foi concluído.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUS_OPCOES)],
        ]);

        DB::transaction(function () use ($data, $atendimento) {
            $updates = ['status' => $data['status']];

            if ($data['status'] === 'Concluído') {
                $updates['data_hora_retorno'] = now();
            }

            $atendimento->update($updates);

            if ($data['status'] === 'Concluído') {
                if ($atendimento->equipe && $atendimento->equipe->ativo) {
                    $atendimento->equipe->update(['status' => 'Disponível']);
                }

                if ($atendimento->viatura && $atendimento->viatura->ativo) {
                    $atendimento->viatura->update(['status' => 'Disponível']);
                }
            }
        });

        return back()->with('success', 'Status do atendimento atualizado.');
    }
}
