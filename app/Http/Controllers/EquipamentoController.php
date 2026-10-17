<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\TipoEquipamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipamentoController extends Controller
{
    private const STATUS_OPCOES = ['Disponível', 'Em Uso', 'Em Manutenção', 'Indisponível'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');

        $equipamentos = Equipamento::with('tipoEquipamento')
            ->when($busca, fn ($q) => $q->where('nome', 'ilike', "%{$busca}%"))
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        $tipos = TipoEquipamento::where('ativo', true)->orderBy('nome')->get();

        return view('equipamentos.index', [
            'equipamentos' => $equipamentos,
            'busca' => $busca,
            'tipos' => $tipos,
            'statusOpcoes' => self::STATUS_OPCOES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('equipamento', [
            'tipo_equipamento' => ['required', 'string', 'max:100'],
            'nome' => ['required', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
        ]);

        $data['tipo_equipamento_id'] = $this->localizarOuCriarTipo($data['tipo_equipamento'])->id;
        unset($data['tipo_equipamento']);

        $data['status'] = 'Disponível';
        $data['disponibilidade'] = 'Sim';
        $data['ativo'] = true;

        Equipamento::create($data);

        return redirect()->route('equipamentos.index')->with('success', 'Equipamento cadastrado com sucesso.');
    }

    private function localizarOuCriarTipo(string $nome): TipoEquipamento
    {
        $nome = trim($nome);

        $tipo = TipoEquipamento::whereRaw('lower(nome) = ?', [mb_strtolower($nome)])->first();

        return $tipo ?? TipoEquipamento::create(['nome' => $nome, 'ativo' => true]);
    }

    public function edit(Equipamento $equipamento)
    {
        $tipos = TipoEquipamento::where('ativo', true)->orderBy('nome')->get();

        return view('equipamentos.edit', [
            'equipamento' => $equipamento,
            'tipos' => $tipos,
        ]);
    }

    public function update(Request $request, Equipamento $equipamento)
    {
        $data = $request->validate([
            'tipo_equipamento' => ['required', 'string', 'max:100'],
            'nome' => ['required', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['tipo_equipamento_id'] = $this->localizarOuCriarTipo($data['tipo_equipamento'])->id;
        unset($data['tipo_equipamento']);

        $data['ativo'] = $request->boolean('ativo');

        $equipamento->update($data);

        return redirect()->route('equipamentos.index')->with('success', 'Equipamento atualizado com sucesso.');
    }

    public function status(Request $request, Equipamento $equipamento)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUS_OPCOES)],
        ]);

        $equipamento->update([
            'status' => $data['status'],
            'disponibilidade' => $data['status'] === 'Disponível' ? 'Sim' : 'Não',
        ]);

        return redirect()->route('equipamentos.index')->with('success', "Status de \"{$equipamento->nome}\" atualizado para \"{$data['status']}\".");
    }
}
