<?php

namespace App\Http\Controllers;

use App\Models\Viatura;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViaturaController extends Controller
{
    private const STATUS_OPCOES = ['Disponível', 'Em Atendimento', 'Em Manutenção'];

    public const TIPO_OPCOES = ['Comando', 'Resgate', 'Ambulância', 'Caminhão-Pipa', 'Apoio 4x4', 'Transporte', 'Outro'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $status = $request->query('status');

        $viaturas = Viatura::when($busca, fn ($q) => $q->where('placa', 'ilike', "%{$busca}%")
                ->orWhere('identificacao', 'ilike', "%{$busca}%"))
            ->when($status === 'ativos', fn ($q) => $q->where('ativo', true))
            ->when($status === 'inativos', fn ($q) => $q->where('ativo', false))
            ->orderBy('identificacao')
            ->paginate(9)
            ->withQueryString();

        $viaturas->getCollection()->each(function (Viatura $viatura) {
            if ($viatura->status === 'Em Atendimento') {
                $viatura->atendimentoAtual = $viatura->atendimentos()
                    ->whereIn('status', ['Em Deslocamento', 'No Local'])
                    ->with(['equipe', 'ocorrencia'])
                    ->latest('data_hora_saida')
                    ->first();
            }
        });

        return view('viaturas.index', [
            'viaturas' => $viaturas,
            'busca' => $busca,
            'status' => $status,
            'statusOpcoes' => self::STATUS_OPCOES,
            'tipoOpcoes' => self::TIPO_OPCOES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('viatura', [
            'placa' => ['required', 'string', 'max:10', 'unique:viaturas,placa'],
            'identificacao' => ['required', 'string', 'max:50'],
            'tipo' => ['required', Rule::in(self::TIPO_OPCOES)],
            'modelo' => ['nullable', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $data['placa'] = strtoupper($data['placa']);
        $data['status'] = 'Disponível';
        $data['disponibilidade'] = 'Sim';
        $data['ativo'] = true;

        Viatura::create($data);

        return redirect()->route('viaturas.index')->with('success', 'Viatura cadastrada com sucesso.');
    }

    public function edit(Viatura $viatura)
    {
        return view('viaturas.edit', [
            'viatura' => $viatura,
            'tipoOpcoes' => self::TIPO_OPCOES,
        ]);
    }

    public function update(Request $request, Viatura $viatura)
    {
        $data = $request->validate([
            'placa' => ['required', 'string', 'max:10', Rule::unique('viaturas', 'placa')->ignore($viatura->id)],
            'identificacao' => ['required', 'string', 'max:50'],
            'tipo' => ['required', Rule::in(self::TIPO_OPCOES)],
            'modelo' => ['nullable', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['placa'] = strtoupper($data['placa']);
        $data['ativo'] = $request->boolean('ativo');

        $viatura->update($data);

        return redirect()->route('viaturas.index')->with('success', 'Viatura atualizada com sucesso.');
    }

    public function status(Request $request, Viatura $viatura)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUS_OPCOES)],
        ]);

        $viatura->update([
            'status' => $data['status'],
            'disponibilidade' => $data['status'] === 'Disponível' ? 'Sim' : 'Não',
        ]);

        return back()->with('success', "Status de \"{$viatura->identificacao}\" atualizado para \"{$data['status']}\".");
    }

    public function toggleAtivo(Viatura $viatura)
    {
        if ($viatura->ativo && $viatura->status === 'Em Atendimento') {
            return back()->with('error', "Não é possível inativar \"{$viatura->identificacao}\": a viatura está em atendimento.");
        }

        $viatura->update(['ativo' => ! $viatura->ativo]);

        $mensagem = $viatura->ativo
            ? "Viatura \"{$viatura->identificacao}\" reativada."
            : "Viatura \"{$viatura->identificacao}\" inativada.";

        return back()->with('success', $mensagem);
    }
}
