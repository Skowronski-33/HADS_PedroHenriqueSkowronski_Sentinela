<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use App\Models\Donativo;
use App\Models\DonativoTransferencia;
use App\Models\MovimentacaoDonativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DonativoController extends Controller
{
    public const UNIDADE_OPCOES = [
        'Unidades', 'Litros', 'Quilogramas (kg)', 'Gramas (g)',
        'Caixas', 'Pacotes', 'Cestas Básicas', 'Pares', 'Conjuntos',
    ];

    public function index(Request $request, Abrigo $abrigo)
    {
        $busca = $request->query('busca');

        $donativos = $abrigo->donativos()
            ->when($busca, fn ($q) => $q->where('tipo', 'ilike', "%{$busca}%"))
            ->orderBy('tipo')
            ->paginate(10)
            ->withQueryString();

        $outrosAbrigos = Abrigo::where('id', '!=', $abrigo->id)->where('ativo', true)->orderBy('nome')->get();

        return view('donativos.index', [
            'abrigo' => $abrigo,
            'donativos' => $donativos,
            'busca' => $busca,
            'outrosAbrigos' => $outrosAbrigos,
            'unidadeOpcoes' => self::UNIDADE_OPCOES,
        ]);
    }

    public function store(Request $request, Abrigo $abrigo)
    {
        $data = $request->validateWithBag('donativo', [
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:200'],
            'unidade_medida' => ['required', Rule::in(self::UNIDADE_OPCOES)],
        ]);

        $abrigo->donativos()->create($data + ['estoque_atual' => 0, 'ativo' => true]);

        return redirect()->route('abrigos.donativos.index', $abrigo)->with('success', 'Item de donativo cadastrado com sucesso.');
    }

    public function update(Request $request, Abrigo $abrigo, Donativo $donativo)
    {
        abort_unless($donativo->abrigo_id === $abrigo->id, 404);

        $data = $request->validateWithBag('donativo_edit', [
            'tipo' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:200'],
            'unidade_medida' => ['required', Rule::in(self::UNIDADE_OPCOES)],
        ]);

        $donativo->update($data);

        return redirect()->route('abrigos.donativos.index', $abrigo)->with('success', 'Item de donativo atualizado com sucesso.');
    }

    public function entrada(Request $request, Abrigo $abrigo, Donativo $donativo)
    {
        $data = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $abrigo, $donativo, $request) {
            $donativo->increment('estoque_atual', $data['quantidade']);

            MovimentacaoDonativo::create([
                'donativo_id' => $donativo->id,
                'user_id' => $request->user()->id,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $data['quantidade'],
                'data_hora' => now(),
                'observacoes' => $data['observacoes'] ?? null,
            ]);
        });

        return redirect()->route('abrigos.donativos.index', $abrigo)
            ->with('success', "Entrada de {$data['quantidade']} {$donativo->unidade_medida} de \"{$donativo->tipo}\" registrada com sucesso.");
    }

    public function saida(Request $request, Abrigo $abrigo, Donativo $donativo)
    {
        $data = $request->validate([
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ]);

        if ($data['quantidade'] > $donativo->estoque_atual) {
            return redirect()->route('abrigos.donativos.index', $abrigo)
                ->with('error', "Saída não permitida: estoque atual de \"{$donativo->tipo}\" é {$donativo->estoque_atual} {$donativo->unidade_medida}.");
        }

        DB::transaction(function () use ($data, $abrigo, $donativo, $request) {
            $donativo->decrement('estoque_atual', $data['quantidade']);

            MovimentacaoDonativo::create([
                'donativo_id' => $donativo->id,
                'user_id' => $request->user()->id,
                'tipo_movimentacao' => 'saida',
                'quantidade' => $data['quantidade'],
                'data_hora' => now(),
                'observacoes' => $data['observacoes'] ?? null,
            ]);
        });

        return redirect()->route('abrigos.donativos.index', $abrigo)->with('success', 'Saída de donativo registrada com sucesso.');
    }

    public function transferir(Request $request, Abrigo $abrigo, Donativo $donativo)
    {
        $data = $request->validate([
            'abrigo_destino_id' => ['required', 'integer', 'exists:abrigos,id'],
            'quantidade' => ['required', 'integer', 'min:1'],
            'observacoes' => ['nullable', 'string'],
        ], [], ['abrigo_destino_id' => 'abrigo de destino']);

        if ((int) $data['abrigo_destino_id'] === $abrigo->id) {
            return redirect()->route('abrigos.donativos.index', $abrigo)
                ->with('error', 'O abrigo de destino deve ser diferente do abrigo atual.');
        }

        if ($data['quantidade'] > $donativo->estoque_atual) {
            return redirect()->route('abrigos.donativos.index', $abrigo)
                ->with('error', "Transferência não permitida: estoque atual de \"{$donativo->tipo}\" é {$donativo->estoque_atual} {$donativo->unidade_medida}.");
        }

        $destino = Abrigo::findOrFail($data['abrigo_destino_id']);

        DB::transaction(function () use ($data, $abrigo, $donativo, $destino, $request) {
            $donativo->decrement('estoque_atual', $data['quantidade']);

            // Localiza o mesmo item no abrigo de destino (mesmo tipo + unidade) ou cria um novo registro.
            $donativoDestino = $destino->donativos()
                ->whereRaw('lower(tipo) = ?', [mb_strtolower($donativo->tipo)])
                ->whereRaw('lower(unidade_medida) = ?', [mb_strtolower($donativo->unidade_medida)])
                ->first();

            if (! $donativoDestino) {
                $donativoDestino = $destino->donativos()->create([
                    'tipo' => $donativo->tipo,
                    'descricao' => $donativo->descricao,
                    'unidade_medida' => $donativo->unidade_medida,
                    'estoque_atual' => 0,
                    'ativo' => true,
                ]);
            }

            $donativoDestino->increment('estoque_atual', $data['quantidade']);

            $nota = $data['observacoes'] ?? null;

            MovimentacaoDonativo::create([
                'donativo_id' => $donativo->id,
                'user_id' => $request->user()->id,
                'tipo_movimentacao' => 'saida',
                'quantidade' => $data['quantidade'],
                'data_hora' => now(),
                'observacoes' => "Transferência para \"{$destino->nome}\"".($nota ? " — {$nota}" : ''),
            ]);

            MovimentacaoDonativo::create([
                'donativo_id' => $donativoDestino->id,
                'user_id' => $request->user()->id,
                'tipo_movimentacao' => 'entrada',
                'quantidade' => $data['quantidade'],
                'data_hora' => now(),
                'observacoes' => "Transferência de \"{$abrigo->nome}\"".($nota ? " — {$nota}" : ''),
            ]);

            DonativoTransferencia::create([
                'donativo_origem_id' => $donativo->id,
                'donativo_destino_id' => $donativoDestino->id,
                'quantidade' => $data['quantidade'],
                'user_id' => $request->user()->id,
                'data_hora' => now(),
                'observacoes' => $nota,
            ]);
        });

        return redirect()->route('abrigos.donativos.index', $abrigo)
            ->with('success', "Transferência de {$data['quantidade']} {$donativo->unidade_medida} de \"{$donativo->tipo}\" para \"{$destino->nome}\" registrada com sucesso.");
    }
}
