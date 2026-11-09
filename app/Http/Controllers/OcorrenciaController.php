<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\Equipe;
use App\Models\Ocorrencia;
use App\Models\Prioridade;
use App\Models\TipoOcorrencia;
use App\Models\Viatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OcorrenciaController extends Controller
{
    public const STATUS_OPCOES = ['Aberta', 'Triada', 'Em Atendimento', 'Encerrada', 'Descartada'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $status = $request->query('status');
        $prioridadeId = $request->query('prioridade_id');
        $dataInicio = $request->query('data_inicio');
        $dataFim = $request->query('data_fim');

        $ocorrencias = Ocorrencia::with(['tipoOcorrencia', 'prioridade'])
            ->when($busca, fn ($q) => $q->where(function ($q2) use ($busca) {
                $q2->where('endereco', 'ilike', "%{$busca}%")
                    ->orWhere('bairro', 'ilike', "%{$busca}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($prioridadeId, fn ($q) => $q->where('prioridade_id', $prioridadeId))
            ->when($dataInicio, fn ($q) => $q->whereDate('data_hora', '>=', $dataInicio))
            ->when($dataFim, fn ($q) => $q->whereDate('data_hora', '<=', $dataFim))
            ->orderByDesc('data_hora')
            ->paginate(15)
            ->withQueryString();

        return view('ocorrencias.index', [
            'ocorrencias' => $ocorrencias,
            'busca' => $busca,
            'status' => $status,
            'prioridadeId' => $prioridadeId,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'statusOpcoes' => self::STATUS_OPCOES,
            'prioridades' => Prioridade::where('ativo', true)->orderBy('id')->get(),
            'tiposComContagem' => TipoOcorrencia::where('ativo', true)->withCount('ocorrencias')->orderBy('nome')->get(),
            'prioridadesComContagem' => Prioridade::where('ativo', true)->withCount('ocorrencias')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('ocorrencias.create', [
            'tipos' => TipoOcorrencia::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_ocorrencia_id' => ['required', 'exists:tipos_ocorrencia,id'],
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
            'equipamentosDisponiveis' => Equipamento::where('ativo', true)->where('status', 'Disponível')->orderBy('nome')->get(),
            'atendimentoStatusOpcoes' => AtendimentoController::STATUS_OPCOES,
            'prioridades' => Prioridade::where('ativo', true)->orderBy('id')->get(),
        ]);
    }

    public function edit(Ocorrencia $ocorrencia)
    {
        return view('ocorrencias.edit', [
            'ocorrencia' => $ocorrencia,
            'tipos' => TipoOcorrencia::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Ocorrencia $ocorrencia)
    {
        $data = $request->validate([
            'tipo_ocorrencia_id' => ['required', 'exists:tipos_ocorrencia,id'],
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
        if (in_array($ocorrencia->status, ['Encerrada', 'Descartada'])) {
            return back()->with('error', 'Esta ocorrência já está encerrada ou descartada.');
        }

        DB::transaction(function () use ($ocorrencia) {
            $atendimentosAbertos = $ocorrencia->atendimentos()
                ->whereIn('status', ['Em Deslocamento', 'No Local'])
                ->with(['equipe', 'viatura'])
                ->get();

            foreach ($atendimentosAbertos as $atendimento) {
                $atendimento->update(['status' => 'Concluído', 'data_hora_retorno' => now()]);

                if ($atendimento->equipe && $atendimento->equipe->ativo) {
                    $atendimento->equipe->update(['status' => 'Disponível']);
                }

                if ($atendimento->viatura && $atendimento->viatura->ativo) {
                    $atendimento->viatura->update(['status' => 'Disponível']);
                }
            }

            $ocorrencia->update(['status' => 'Encerrada']);
        });

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Ocorrência encerrada com sucesso.');
    }

    public function triar(Ocorrencia $ocorrencia)
    {
        if ($ocorrencia->status !== 'Aberta') {
            return back()->with('error', 'Apenas ocorrências em aberto podem ser triadas.');
        }

        $ocorrencia->update(['status' => 'Triada']);

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Ocorrência triada com sucesso. Agora ela pode ser classificada.');
    }

    public function classificar(Request $request, Ocorrencia $ocorrencia)
    {
        if (! in_array($ocorrencia->status, ['Triada', 'Em Atendimento'])) {
            return back()->with('error', 'A ocorrência precisa ser triada antes de ser classificada.');
        }

        $data = $request->validate([
            'prioridade_id' => ['required', 'exists:prioridades,id'],
        ]);

        $ocorrencia->update($data);

        return redirect()->route('ocorrencias.show', $ocorrencia)->with('success', 'Ocorrência classificada com sucesso. Agora ela pode ser despachada.');
    }

    public function descartar(Ocorrencia $ocorrencia)
    {
        if (! in_array($ocorrencia->status, ['Aberta', 'Triada'])) {
            return back()->with('error', 'Esta ocorrência não pode mais ser descartada.');
        }

        if ($ocorrencia->atendimentos()->count() > 0) {
            return back()->with('error', 'Não é possível descartar: já há atendimento(s) despachado(s) para esta ocorrência.');
        }

        $ocorrencia->update(['status' => 'Descartada']);

        return redirect()->route('ocorrencias.index')->with('success', 'Ocorrência descartada com sucesso.');
    }
}
