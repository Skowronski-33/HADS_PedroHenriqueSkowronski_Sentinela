<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipe extends Model
{
    protected $fillable = [
        'tipo_equipe_id',
        'nome',
        'status',
        'ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function tipoEquipe()
    {
        return $this->belongsTo(TipoEquipe::class);
    }

    public function voluntarios(): BelongsToMany
    {
        return $this->belongsToMany(Voluntario::class, 'equipe_voluntario');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class);
    }
}
