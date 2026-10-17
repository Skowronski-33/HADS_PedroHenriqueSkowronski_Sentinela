<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use App\Models\Equipamento;
use App\Models\PessoaAbrigada;
use App\Models\Viatura;

class DashboardController extends Controller
{
    public function index()
    {
        $abrigosAtivos = Abrigo::where('ativo', true)->count();
        $pessoasAbrigadas = PessoaAbrigada::whereNull('data_saida')->count();
        $viaturasDisponiveis = Viatura::where('ativo', true)->where('status', 'Disponível')->count();
        $equipamentosDisponiveis = Equipamento::where('ativo', true)->where('status', 'Disponível')->count();

        return view('dashboard', compact('abrigosAtivos', 'pessoasAbrigadas', 'viaturasDisponiveis', 'equipamentosDisponiveis'));
    }
}
