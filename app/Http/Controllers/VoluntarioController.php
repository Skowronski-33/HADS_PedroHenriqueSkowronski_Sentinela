<?php

namespace App\Http\Controllers;

use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoluntarioController extends Controller
{
    private const DISPONIBILIDADE_OPCOES = ['Disponível', 'Indisponível'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $status = $request->query('status');

        $voluntarios = Voluntario::when($busca, fn ($q) => $q->where('nome', 'ilike', "%{$busca}%"))
            ->when($status === 'ativos', fn ($q) => $q->where('ativo', true)->where('disponibilidade', 'Disponível'))
            ->when($status === 'inativos', fn ($q) => $q->where(function ($q2) {
                $q2->where('ativo', false)->orWhere('disponibilidade', 'Indisponível');
            }))
            ->orderBy('nome')
            ->paginate(9)
            ->withQueryString();

        return view('voluntarios.index', [
            'voluntarios' => $voluntarios,
            'busca' => $busca,
            'status' => $status,
            'disponibilidadeOpcoes' => self::DISPONIBILIDADE_OPCOES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validateWithBag('voluntario', [
            'nome' => ['required', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'habilidade' => ['nullable', 'string', 'max:100'],
        ]);

        $data['disponibilidade'] = 'Disponível';
        $data['ativo'] = true;

        Voluntario::create($data);

        return redirect()->route('voluntarios.index')->with('success', 'Voluntário cadastrado com sucesso.');
    }

    public function edit(Voluntario $voluntario)
    {
        return view('voluntarios.edit', [
            'voluntario' => $voluntario,
        ]);
    }

    public function update(Request $request, Voluntario $voluntario)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'habilidade' => ['nullable', 'string', 'max:100'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $data['ativo'] = $request->boolean('ativo');

        $voluntario->update($data);

        return redirect()->route('voluntarios.index')->with('success', 'Voluntário atualizado com sucesso.');
    }

    public function disponibilidade(Request $request, Voluntario $voluntario)
    {
        $data = $request->validate([
            'disponibilidade' => ['required', Rule::in(self::DISPONIBILIDADE_OPCOES)],
        ]);

        $voluntario->update(['disponibilidade' => $data['disponibilidade']]);

        return back()->with('success', "Disponibilidade de \"{$voluntario->nome}\" atualizada para \"{$data['disponibilidade']}\".");
    }

    public function toggleAtivo(Voluntario $voluntario)
    {
        $voluntario->update(['ativo' => ! $voluntario->ativo]);

        $mensagem = $voluntario->ativo
            ? "Voluntário \"{$voluntario->nome}\" reativado."
            : "Voluntário \"{$voluntario->nome}\" inativado.";

        return back()->with('success', $mensagem);
    }

    public function destroy(Voluntario $voluntario)
    {
        $emEquipes = $voluntario->equipes()->count();

        if ($emEquipes > 0) {
            return redirect()->route('voluntarios.index')
                ->with('error', "Não é possível excluir \"{$voluntario->nome}\": ele está vinculado a {$emEquipes} equipe(s).");
        }

        $nome = $voluntario->nome;
        $voluntario->delete();

        return redirect()->route('voluntarios.index')->with('success', "Voluntário \"{$nome}\" excluído com sucesso.");
    }
}
