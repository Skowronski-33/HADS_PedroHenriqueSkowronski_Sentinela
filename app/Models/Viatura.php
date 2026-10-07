<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viatura extends Model
{
    protected $fillable = [
        'placa',
        'identificacao',
        'modelo',
        'status',
        'disponibilidade',
        'ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class);
    }
}
