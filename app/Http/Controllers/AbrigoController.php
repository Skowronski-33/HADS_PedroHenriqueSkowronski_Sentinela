<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbrigoController extends Controller
{
    public const STATUS_OPCOES = ['Ativo', 'Lotado', 'Inativo'];

    public function index()
    {
        $abrigos = Abrigo::orderBy('nome')->paginate(15);

        foreach ($abrigos as $abrigo) {
            $abrigo->ocupacao_atual = $abrigo->ocupacaoAtual();
        }

        return view('abrigos.index', compact('abrigos'));
    }

    public function create()
    {
        return view('abrigos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'endereco' => ['required', 'string', 'max:200'],
            'numero' => ['nullable', 'string', 'max:10'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['required', 'string', 'max:100'],
            'capacidade' => ['required', 'integer', 'min:1'],
            'responsavel' => ['nullable', 'string', 'max:100'],
            'telefone_responsavel' => ['nullable', 'string', 'max:20'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $data['status'] = 'Ativo';
        $data['ativo'] = true;

        Abrigo::create($data);

        return redirect()->route('abrigos.index')->with('success', 'Abrigo cadastrado com sucesso.');
    }

    public function show(Request $request, Abrigo $abrigo)
    {
        $abrigo->load(['donativos' => fn ($q) => $q->orderBy('tipo')]);

        $busca = $request->query('busca');

        $pessoas = $abrigo->pessoasAtivas()
            ->when($busca, fn ($q) => $q->where('nome', 'ilike', "%{$busca}%"))
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        $ocupacaoAtual = $abrigo->ocupacaoAtual();
        $outrosAbrigos = Abrigo::where('id', '!=', $abrigo->id)->where('ativo', true)->orderBy('nome')->get();

        // Possíveis responsáveis familiares: pessoas ativas deste abrigo que ainda não são dependentes de ninguém
        $responsaveisDisponiveis = $abrigo->pessoasAtivas()
            ->whereNull('responsavel_familiar_id')
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return view('abrigos.show', compact('abrigo', 'pessoas', 'busca', 'ocupacaoAtual', 'outrosAbrigos', 'responsaveisDisponiveis'));
    }

    public function edit(Abrigo $abrigo)
    {
        $statusOpcoes = self::STATUS_OPCOES;

        return view('abrigos.edit', compact('abrigo', 'statusOpcoes'));
    }

    public function update(Request $request, Abrigo $abrigo)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'endereco' => ['required', 'string', 'max:200'],
            'numero' => ['nullable', 'string', 'max:10'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['required', 'string', 'max:100'],
            'capacidade' => ['required', 'integer', 'min:1'],
            'responsavel' => ['nullable', 'string', 'max:100'],
            'telefone_responsavel' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(self::STATUS_OPCOES)],
            'observacoes' => ['nullable', 'string'],
        ]);

        // "Ativo" é derivado do status: só "Inativo" marca o abrigo como inativo.
        $data['ativo'] = $data['status'] !== 'Inativo';

        $estaSendoInativado = $abrigo->status !== 'Inativo' && $data['status'] === 'Inativo';

        if ($estaSendoInativado) {
            $pessoasAtivas = $abrigo->ocupacaoAtual();
            $donativosComEstoque = $abrigo->donativos()->where('estoque_atual', '>', 0)->count();

            if ($pessoasAtivas > 0 || $donativosComEstoque > 0) {
                $pendencias = [];

                if ($pessoasAtivas > 0) {
                    $pendencias[] = "{$pessoasAtivas} pessoa(s) ainda abrigada(s)";
                }

                if ($donativosComEstoque > 0) {
                    $pendencias[] = "{$donativosComEstoque} item(ns) de donativo com estoque";
                }

                return redirect()->route('abrigos.edit', $abrigo)
                    ->with('error', 'Não é possível inativar o abrigo: ainda há '.implode(' e ', $pendencias).
                        '. Registre a saída ou transfira para outro abrigo antes de inativar.');
            }
        }

        $abrigo->update($data);

        return redirect()->route('abrigos.index')->with('success', 'Abrigo atualizado com sucesso.');
    }
}
