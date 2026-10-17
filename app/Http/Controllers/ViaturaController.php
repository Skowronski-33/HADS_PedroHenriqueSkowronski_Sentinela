<?php

namespace App\Http\Controllers;

use App\Models\Viatura;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViaturaController extends Controller
{
    private const STATUS_OPCOES = ['Disponível', 'Em Atendimento', 'Em Manutenção'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');

        $viaturas = Viatura::when($busca, fn ($q) => $q->where('placa', 'ilike', "%{$busca}%")
                ->orWhere('identificacao', 'ilike', "%{$busca}%"))
            ->orderBy('identificacao')
            ->paginate(10)
            ->withQueryString();

        return view('viaturas.index', [
            'viaturas' => $viaturas,
            'busca' => $busca,
            'statusOpcoes' => self::STATUS_OPCOES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('viatura', [
            'placa' => ['required', 'string', 'max:10', 'unique:viaturas,placa'],
            'identificacao' => ['required', 'string', 'max:50'],
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
        return view('viaturas.edit', compact('viatura'));
    }

    public function update(Request $request, Viatura $viatura)
    {
        $data = $request->validate([
            'placa' => ['required', 'string', 'max:10', Rule::unique('viaturas', 'placa')->ignore($viatura->id)],
            'identificacao' => ['required', 'string', 'max:50'],
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

        return redirect()->route('viaturas.index')->with('success', "Status de \"{$viatura->identificacao}\" atualizado para \"{$data['status']}\".");
    }
}
