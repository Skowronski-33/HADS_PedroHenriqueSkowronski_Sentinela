<?php

namespace App\Providers;

use App\Models\Abrigo;
use App\Models\Atendimento;
use App\Models\Donativo;
use App\Models\Equipamento;
use App\Models\Equipe;
use App\Models\Ocorrencia;
use App\Models\PessoaAbrigada;
use App\Models\Viatura;
use App\Models\Voluntario;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Nomes curtos para o "subject_type" do log de atividades (ActivityLog),
        // em vez do nome completo da classe PHP. Usa morphMap() (não enforceMorphMap):
        // o Spatie Permission usa App\Models\User em relações polimórficas próprias
        // (model_has_roles) sem estar nesse mapa, e "enforce" faria isso explodir.
        Relation::morphMap([
            'ocorrencia' => Ocorrencia::class,
            'atendimento' => Atendimento::class,
            'viatura' => Viatura::class,
            'equipamento' => Equipamento::class,
            'equipe' => Equipe::class,
            'abrigo' => Abrigo::class,
            'donativo' => Donativo::class,
            'pessoa_abrigada' => PessoaAbrigada::class,
            'voluntario' => Voluntario::class,
        ]);
    }
}
