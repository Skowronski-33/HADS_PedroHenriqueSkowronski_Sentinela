<?php

namespace App\Http\Controllers;

use App\Models\Abrigo;
use App\Models\Equipamento;
use App\Models\Ocorrencia;
use App\Models\PessoaAbrigada;
use App\Models\Viatura;
use App\Models\Voluntario;

class DashboardController extends Controller
{
    public function index()
    {
        $abrigosAtivos = Abrigo::where('ativo', true)->count();
        $pessoasAbrigadas = PessoaAbrigada::whereNull('data_saida')->count();
        $viaturasDisponiveis = Viatura::where('ativo', true)->where('status', 'Disponível')->count();
        $equipamentosDisponiveis = Equipamento::where('ativo', true)->where('status', 'Disponível')->count();
        $voluntariosDisponiveis = Voluntario::where('ativo', true)->where('disponibilidade', 'Disponível')->count();
        $ocorrenciasAbertas = Ocorrencia::whereIn('status', ['Aberta', 'Triada', 'Em Atendimento'])->count();

        return view('dashboard', compact(
            'abrigosAtivos', 'pessoasAbrigadas', 'viaturasDisponiveis',
            'equipamentosDisponiveis', 'voluntariosDisponiveis', 'ocorrenciasAbertas'
        ));
    }
}
