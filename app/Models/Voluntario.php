<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Voluntario extends Model
{
    protected $fillable = [
        'nome',
        'telefone',
        'habilidade',
        'disponibilidade',
        'ativo',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'equipe_voluntario');
    }

    public function corHabilidade(): string
    {
        $habilidade = mb_strtolower($this->habilidade ?? '');

        return match (true) {
            str_contains($habilidade, 'socorro') || str_contains($habilidade, 'médic') || str_contains($habilidade, 'medic') || str_contains($habilidade, 'saúde') => 'red',
            str_contains($habilidade, 'condu') || str_contains($habilidade, 'motorist') || str_contains($habilidade, 'veículo') => 'blue',
            str_contains($habilidade, 'resgate') || str_contains($habilidade, 'aquátic') => 'teal',
            str_contains($habilidade, 'comunica') || str_contains($habilidade, 'rádio') || str_contains($habilidade, 'radio') => 'purple',
            str_contains($habilidade, 'logístic') || str_contains($habilidade, 'apoio') => 'amber',
            str_contains($habilidade, 'culinár') || str_contains($habilidade, 'alimenta') => 'green',
            default => 'slate',
        };
    }
}
