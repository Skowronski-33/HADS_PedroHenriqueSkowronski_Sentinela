<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use Illuminate\Http\Request;

class AbrigoController extends Controller
{
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
            'bairro' => ['nullable', 'string', 'max:100'],
            'capacidade' => ['required', 'integer', 'min:1'],
            'responsavel' => ['nullable', 'string', 'max:100'],
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
        return view('abrigos.edit', compact('abrigo'));
    }

    public function update(Request $request, Abrigo $abrigo)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'endereco' => ['required', 'string', 'max:200'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'capacidade' => ['required', 'integer', 'min:1'],
            'responsavel' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:30'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->boolean('ativo');

        $abrigo->update($data);

        return redirect()->route('abrigos.index')->with('success', 'Abrigo atualizado com sucesso.');
    }
}
