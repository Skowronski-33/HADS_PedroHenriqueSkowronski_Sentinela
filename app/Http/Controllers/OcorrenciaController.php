<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\Equipe;
use App\Models\Ocorrencia;
use App\Models\Prioridade;
use App\Models\TipoOcorrencia;
use App\Models\Viatura;
use Illuminate\Http\Request;

class OcorrenciaController extends Controller
{
    public const STATUS_OPCOES = ['Aberta', 'Em Atendimento', 'Encerrada', 'Cancelada'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $status = $request->query('status');
        $prioridadeId = $request->query('prioridade_id');

        $ocorrencias = Ocorrencia::with(['tipoOcorrencia', 'prioridade'])
            ->when($busca, fn ($q) => $q->where(function ($q2) use ($busca) {
                $q2->where('endereco', 'ilike', "%{$busca}%")
                    ->orWhere('bairro', 'ilike', "%{$busca}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($prioridadeId, fn ($q) => $q->where('prioridade_id', $prioridadeId))
            ->orderByDesc('data_hora')
            ->paginate(15)
            ->withQueryString();

        return view('ocorrencias.index', [
            'ocorrencias' => $ocorrencias,
            'busca' => $busca,
            'status' => $status,
            'prioridadeId' => $prioridadeId,
            'statusOpcoes' => self::STATUS_OPCOES,
            'prioridades' => Prioridade::where('ativo', true)->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('ocorrencias.create', [
            'tipos' => TipoOcorrencia::where('ativo', true)->orderBy('nome')->get(),
            'prioridades' => Prioridade::where('ativo', true)->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_ocorrencia_id' => ['required', 'exists:tipos_ocorrencia,id'],
            'prioridade_id' => ['nullable', 'exists:prioridades,id'],
            'descricao' => ['required', 'string'],
            'endereco' => ['required', 'string', 'max:200'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'ponto_referencia' => ['nullable', 'string', 'max:200'],
            'data_hora' => ['nullable', 'date'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = 'Aberta';
        $data['data_hora'] = $data['data_hora'] ?? now();

        $ocorrencia = Ocorrencia::create($data);

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Ocorrência registrada com sucesso.');
    }

    public function show(Ocorrencia $ocorrencia)
    {
        $ocorrencia->load([
            'tipoOcorrencia',
            'prioridade',
            'usuario',
            'atendimentos' => fn ($q) => $q->orderByDesc('data_hora_saida'),
            'atendimentos.equipe',
            'atendimentos.viatura',
            'atendimentos.equipamentos',
        ]);

        return view('ocorrencias.show', [
            'ocorrencia' => $ocorrencia,
            'equipesDisponiveis' => Equipe::where('ativo', true)->where('status', 'Disponível')->orderBy('nome')->get(),
            'viaturasDisponiveis' => Viatura::where('ativo', true)->where('status', 'Disponível')->orderBy('identificacao')->get(),
            'equipamentosDisponiveis' => Equipamento::where('ativo', true)->orderBy('nome')->get(),
            'atendimentoStatusOpcoes' => AtendimentoController::STATUS_OPCOES,
        ]);
    }

    public function edit(Ocorrencia $ocorrencia)
    {
        return view('ocorrencias.edit', [
            'ocorrencia' => $ocorrencia,
            'tipos' => TipoOcorrencia::where('ativo', true)->orderBy('nome')->get(),
            'prioridades' => Prioridade::where('ativo', true)->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Ocorrencia $ocorrencia)
    {
        $data = $request->validate([
            'tipo_ocorrencia_id' => ['required', 'exists:tipos_ocorrencia,id'],
            'prioridade_id' => ['nullable', 'exists:prioridades,id'],
            'descricao' => ['required', 'string'],
            'endereco' => ['required', 'string', 'max:200'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'ponto_referencia' => ['nullable', 'string', 'max:200'],
            'data_hora' => ['required', 'date'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $ocorrencia->update($data);

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Ocorrência atualizada com sucesso.');
    }

    public function encerrar(Ocorrencia $ocorrencia)
    {
        if (in_array($ocorrencia->status, ['Encerrada', 'Cancelada'])) {
            return back()->with('error', 'Esta ocorrência já está encerrada ou cancelada.');
        }

        $ocorrencia->update(['status' => 'Encerrada']);

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Ocorrência encerrada com sucesso.');
    }

    public function cancelar(Ocorrencia $ocorrencia)
    {
        if (in_array($ocorrencia->status, ['Encerrada', 'Cancelada'])) {
            return back()->with('error', 'Esta ocorrência já está encerrada ou cancelada.');
        }

        if ($ocorrencia->atendimentos()->count() > 0) {
            return back()->with('error', 'Não é possível cancelar: já há atendimento(s) despachado(s) para esta ocorrência.');
        }

        $ocorrencia->update(['status' => 'Cancelada']);

        return redirect()->route('ocorrencias.index')->with('success', 'Ocorrência cancelada com sucesso.');
    }
}
