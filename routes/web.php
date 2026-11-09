<?php

use App\Http\Controllers\AbrigoController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonativoController;
use App\Http\Controllers\EquipamentoController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\OcorrenciaController;
use App\Http\Controllers\PessoaAbrigadaController;
use App\Http\Controllers\PrioridadeController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\TipoEquipamentoController;
use App\Http\Controllers\TipoOcorrenciaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViaturaController;
use App\Http\Controllers\VoluntarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação (visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gerenciamento de usuários e perfis de acesso (RF02) - somente Admin
    Route::middleware('role:Admin')->group(function () {
        Route::resource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'user'])
            ->except(['show', 'destroy']);
        Route::patch('usuarios/{user}/toggle-ativo', [UserController::class, 'toggleAtivo'])
            ->name('usuarios.toggle-ativo');
        Route::patch('usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('usuarios.reset-password');
    });

    // Abrigos, donativos e pessoas abrigadas (RF14, RF15, RF16) - Gerenciador de Abrigo e Admin
    Route::middleware('role:Gerenciador de Abrigo|Admin')->group(function () {
        Route::resource('abrigos', AbrigoController::class)
            ->except(['destroy']);

        Route::get('abrigos/{abrigo}/donativos', [DonativoController::class, 'index'])
            ->name('abrigos.donativos.index');
        Route::post('abrigos/{abrigo}/donativos', [DonativoController::class, 'store'])
            ->name('abrigos.donativos.store');
        Route::patch('abrigos/{abrigo}/donativos/{donativo}', [DonativoController::class, 'update'])
            ->name('abrigos.donativos.update');
        Route::patch('abrigos/{abrigo}/donativos/{donativo}/entrada', [DonativoController::class, 'entrada'])
            ->name('abrigos.donativos.entrada');
        Route::patch('abrigos/{abrigo}/donativos/{donativo}/saida', [DonativoController::class, 'saida'])
            ->name('abrigos.donativos.saida');
        Route::patch('abrigos/{abrigo}/donativos/{donativo}/transferir', [DonativoController::class, 'transferir'])
            ->name('abrigos.donativos.transferir');

        Route::post('abrigos/{abrigo}/pessoas', [PessoaAbrigadaController::class, 'store'])
            ->name('abrigos.pessoas.store');
        Route::patch('abrigos/{abrigo}/pessoas/{pessoa}/saida', [PessoaAbrigadaController::class, 'saida'])
            ->name('abrigos.pessoas.saida');
        Route::patch('abrigos/{abrigo}/pessoas/{pessoa}/transferir', [PessoaAbrigadaController::class, 'transferir'])
            ->name('abrigos.pessoas.transferir');
    });

    // Equipamentos e viaturas (RF09-RF13) - Gerente Operacional e Admin
    Route::middleware('role:Gerente Operacional|Admin')->group(function () {
        Route::get('recursos', [RecursoController::class, 'index'])->name('recursos.index');

        Route::resource('equipamentos', EquipamentoController::class)
            ->except(['show', 'create']);
        Route::patch('equipamentos/{equipamento}/status', [EquipamentoController::class, 'status'])
            ->name('equipamentos.status');
        Route::patch('equipamentos/{equipamento}/toggle-ativo', [EquipamentoController::class, 'toggleAtivo'])
            ->name('equipamentos.toggle-ativo');
        Route::post('tipos-equipamento', [TipoEquipamentoController::class, 'store'])
            ->name('tipos-equipamento.store');
        Route::patch('tipos-equipamento/{tipo}', [TipoEquipamentoController::class, 'update'])
            ->name('tipos-equipamento.update');
        Route::delete('tipos-equipamento/{tipo}', [TipoEquipamentoController::class, 'destroy'])
            ->name('tipos-equipamento.destroy');

        Route::resource('viaturas', ViaturaController::class)
            ->except(['show', 'create', 'destroy']);
        Route::patch('viaturas/{viatura}/status', [ViaturaController::class, 'status'])
            ->name('viaturas.status');
        Route::patch('viaturas/{viatura}/toggle-ativo', [ViaturaController::class, 'toggleAtivo'])
            ->name('viaturas.toggle-ativo');

        // Voluntários (RF08)
        Route::resource('voluntarios', VoluntarioController::class)
            ->except(['show', 'create']);
        Route::patch('voluntarios/{voluntario}/disponibilidade', [VoluntarioController::class, 'disponibilidade'])
            ->name('voluntarios.disponibilidade');
        Route::patch('voluntarios/{voluntario}/toggle-ativo', [VoluntarioController::class, 'toggleAtivo'])
            ->name('voluntarios.toggle-ativo');

        // Equipes
        Route::resource('equipes', EquipeController::class)
            ->except(['show']);
        Route::patch('equipes/{equipe}/status', [EquipeController::class, 'status'])
            ->name('equipes.status');
        Route::patch('equipes/{equipe}/toggle-ativo', [EquipeController::class, 'toggleAtivo'])
            ->name('equipes.toggle-ativo');
    });

    // Ocorrências e atendimentos - Atendente de Ocorrências, Gerente Operacional e Admin
    Route::middleware('role:Atendente de Ocorrências|Gerente Operacional|Admin')->group(function () {
        Route::resource('ocorrencias', OcorrenciaController::class)
            ->except(['destroy']);
        Route::patch('ocorrencias/{ocorrencia}/encerrar', [OcorrenciaController::class, 'encerrar'])
            ->name('ocorrencias.encerrar');
        Route::patch('ocorrencias/{ocorrencia}/triar', [OcorrenciaController::class, 'triar'])
            ->name('ocorrencias.triar');
        Route::patch('ocorrencias/{ocorrencia}/classificar', [OcorrenciaController::class, 'classificar'])
            ->name('ocorrencias.classificar');
        Route::patch('ocorrencias/{ocorrencia}/descartar', [OcorrenciaController::class, 'descartar'])
            ->name('ocorrencias.descartar');

        Route::post('ocorrencias/{ocorrencia}/atendimentos', [AtendimentoController::class, 'store'])
            ->name('ocorrencias.atendimentos.store');
        Route::patch('ocorrencias/{ocorrencia}/atendimentos/{atendimento}/status', [AtendimentoController::class, 'status'])
            ->name('ocorrencias.atendimentos.status');

        Route::post('tipos-ocorrencia', [TipoOcorrenciaController::class, 'store'])
            ->name('tipos-ocorrencia.store');
        Route::patch('tipos-ocorrencia/{tipo}', [TipoOcorrenciaController::class, 'update'])
            ->name('tipos-ocorrencia.update');
        Route::delete('tipos-ocorrencia/{tipo}', [TipoOcorrenciaController::class, 'destroy'])
            ->name('tipos-ocorrencia.destroy');

        Route::post('prioridades', [PrioridadeController::class, 'store'])
            ->name('prioridades.store');
        Route::patch('prioridades/{prioridade}', [PrioridadeController::class, 'update'])
            ->name('prioridades.update');
        Route::delete('prioridades/{prioridade}', [PrioridadeController::class, 'destroy'])
            ->name('prioridades.destroy');
    });
});