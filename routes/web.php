<?php

use App\Http\Controllers\AbrigoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonativoController;
use App\Http\Controllers\PessoaAbrigadaController;
use App\Http\Controllers\UserController;
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
    });

    // Abrigos, donativos e pessoas abrigadas (RF14, RF15, RF16) - Gerenciador de Abrigo e Admin
    Route::middleware('role:Gerenciador de Abrigo|Admin')->group(function () {
        Route::resource('abrigos', AbrigoController::class)
            ->except(['destroy']);

        Route::post('abrigos/{abrigo}/donativos', [DonativoController::class, 'store'])
            ->name('abrigos.donativos.store');
        Route::patch('abrigos/{abrigo}/donativos/{donativo}/entrada', [DonativoController::class, 'entrada'])
            ->name('abrigos.donativos.entrada');
        Route::patch('abrigos/{abrigo}/donativos/{donativo}/saida', [DonativoController::class, 'saida'])
            ->name('abrigos.donativos.saida');

        Route::post('abrigos/{abrigo}/pessoas', [PessoaAbrigadaController::class, 'store'])
            ->name('abrigos.pessoas.store');
        Route::patch('abrigos/{abrigo}/pessoas/{pessoa}/saida', [PessoaAbrigadaController::class, 'saida'])
            ->name('abrigos.pessoas.saida');
        Route::patch('abrigos/{abrigo}/pessoas/{pessoa}/transferir', [PessoaAbrigadaController::class, 'transferir'])
            ->name('abrigos.pessoas.transferir');
    });
});