<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Equipe;
use App\Models\FuncaoEquipe;
use App\Models\TipoEquipe;
use App\Models\Voluntario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipeController extends Controller
{
    private const STATUS_OPCOES = ['Disponível', 'Em Serviço', 'Indisponível'];

    public function index(Request $request)
    {
        $busca = $request->query('busca');
        $status = $request->query('status');

        $equipes = Equipe::with(['tipoEquipe', 'voluntarios'])
            ->when($busca, fn ($q) => $q->where('nome', 'ilike', "%{$busca}%"))
            ->when($status === 'ativos', fn ($q) => $q->where('ativo', true))
            ->when($status === 'inativos', fn ($q) => $q->where('ativo', false))
            ->orderBy('nome')
            ->paginate(9)
            ->withQueryString();

        return view('equipes.index', [
            'equipes' => $equipes,
            'busca' => $busca,
            'status' => $status,
            'statusOpcoes' => self::STATUS_OPCOES,
        ]);
    }

    public function create()
    {
        return view('equipes.create', [
            'tipos' => TipoEquipe::where('ativo', true)->orderBy('nome')->get(),
            'voluntarios' => Voluntario::where('ativo', true)->with('equipes')->orderBy('nome')->get(),
            'funcoesEquipe' => FuncaoEquipe::where('ativo', true)->orderBy('nome')->get(),
            'funcoesEquipeMap' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_equipe_id' => ['required', 'exists:tipos_equipe,id'],
            'nome' => ['required', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
            'voluntarios' => ['nullable', 'array'],
            'voluntarios.*' => ['exists:voluntarios,id'],
            'funcoes' => ['nullable', 'array'],
            'funcoes.*' => ['nullable', 'string', 'max:100'],
        ]);

        $voluntarios = $data['voluntarios'] ?? [];
        $funcoes = $data['funcoes'] ?? [];
        unset($data['voluntarios'], $data['funcoes']);

        $data['status'] = 'Disponível';
        $data['ativo'] = true;

        $equipe = Equipe::create($data);
        $equipe->voluntarios()->sync($this->montarPivotMembros($voluntarios, $funcoes));

        return redirect()->route('equipes.index')->with('success', 'Equipe cadastrada com sucesso.');
    }

    public function edit(Equipe $equipe)
    {
        $equipe->load('voluntarios');

        $voluntarios = Voluntario::where('ativo', true)
            ->orWhereIn('id', $equipe->voluntarios->pluck('id'))
            ->with('equipes')
            ->orderBy('nome')
            ->get();

        return view('equipes.edit', [
            'equipe' => $equipe,
            'tipos' => TipoEquipe::where('ativo', true)->orderBy('nome')->get(),
            'voluntarios' => $voluntarios,
            'funcoesEquipe' => FuncaoEquipe::where('ativo', true)->orderBy('nome')->get(),
            'funcoesEquipeMap' => FuncaoEquipe::pluck('nome', 'id'),
        ]);
    }

    public function update(Request $request, Equipe $equipe)
    {
        $data = $request->validate([
            'tipo_equipe_id' => ['required', 'exists:tipos_equipe,id'],
            'nome' => ['required', 'string', 'max:100'],
            'observacoes' => ['nullable', 'string'],
            'ativo' => ['nullable', 'boolean'],
            'voluntarios' => ['nullable', 'array'],
            'voluntarios.*' => ['exists:voluntarios,id'],
            'funcoes' => ['nullable', 'array'],
            'funcoes.*' => ['nullable', 'string', 'max:100'],
        ]);

        $voluntarios = $data['voluntarios'] ?? [];
        $funcoes = $data['funcoes'] ?? [];
        unset($data['voluntarios'], $data['funcoes']);

        $data['ativo'] = $request->boolean('ativo');

        $equipe->update($data);
        $equipe->voluntarios()->sync($this->montarPivotMembros($voluntarios, $funcoes));

        return redirect()->route('equipes.index')->with('success', 'Equipe atualizada com sucesso.');
    }

    /**
     * Monta o array de sync() do pivô equipe_voluntario, resolvendo o texto livre de
     * função (digitado no combobox) para o id de uma função existente ou recém-criada.
     *
     * @param  array<int, int|string>  $voluntarios
     * @param  array<int|string, string|null>  $funcoes
     */
    private function montarPivotMembros(array $voluntarios, array $funcoes): array
    {
        $pivot = [];

        foreach ($voluntarios as $voluntarioId) {
            $nomeFuncao = trim((string) ($funcoes[$voluntarioId] ?? ''));

            $pivot[$voluntarioId] = [
                'funcao_equipe_id' => $nomeFuncao !== '' ? $this->localizarOuCriarFuncao($nomeFuncao)->id : null,
            ];
        }

        return $pivot;
    }

    private function localizarOuCriarFuncao(string $nome): FuncaoEquipe
    {
        $nome = trim($nome);

        $funcao = FuncaoEquipe::whereRaw('lower(nome) = ?', [mb_strtolower($nome)])->first();

        return $funcao ?? FuncaoEquipe::create(['nome' => $nome, 'ativo' => true]);
    }

    public function status(Request $request, Equipe $equipe)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUS_OPCOES)],
        ]);

        $equipe->update(['status' => $data['status']]);

        ActivityLog::registrar(
            'equipe.status_alterado',
            "Status da equipe \"{$equipe->nome}\" alterado para \"{$data['status']}\".",
            $equipe
        );

        return back()->with('success', "Status da equipe \"{$equipe->nome}\" atualizado para \"{$data['status']}\".");
    }

    public function toggleAtivo(Equipe $equipe)
    {
        if ($equipe->ativo && $equipe->status === 'Em Serviço') {
            return back()->with('error', "Não é possível inativar \"{$equipe->nome}\": a equipe está em serviço em um atendimento.");
        }

        $equipe->update(['ativo' => ! $equipe->ativo]);

        $mensagem = $equipe->ativo
            ? "Equipe \"{$equipe->nome}\" reativada."
            : "Equipe \"{$equipe->nome}\" inativada.";

        ActivityLog::registrar($equipe->ativo ? 'equipe.reativada' : 'equipe.inativada', $mensagem, $equipe);

        return back()->with('success', $mensagem);
    }

    public function destroy(Equipe $equipe)
    {
        $emAtendimentos = $equipe->atendimentos()->count();

        if ($emAtendimentos > 0) {
            return redirect()->route('equipes.index')
                ->with('error', "Não é possível excluir \"{$equipe->nome}\": ela está vinculada a {$emAtendimentos} atendimento(s).");
        }

        $nome = $equipe->nome;
        $equipe->delete();

        return redirect()->route('equipes.index')->with('success', "Equipe \"{$nome}\" excluída com sucesso.");
    }
}
