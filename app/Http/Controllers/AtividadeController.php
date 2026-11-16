<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AtividadeController extends Controller
{
    public function index(Request $request)
    {
        $usuarioId = $request->query('usuario_id');
        $busca = $request->query('busca');
        $dataInicio = $request->query('data_inicio');
        $dataFim = $request->query('data_fim');

        $atividades = ActivityLog::with('usuario')
            ->when($usuarioId, fn ($q) => $q->where('user_id', $usuarioId))
            ->when($busca, fn ($q) => $q->where('descricao', 'ilike', "%{$busca}%"))
            ->when($dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $dataInicio))
            ->when($dataFim, fn ($q) => $q->whereDate('created_at', '<=', $dataFim))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('atividades.index', [
            'atividades' => $atividades,
            'usuarios' => User::orderBy('name')->get(['id', 'name']),
            'usuarioId' => $usuarioId,
            'busca' => $busca,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
        ]);
    }
}
