<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donativo extends Model
{
    protected $fillable = [
        'abrigo_id',
        'tipo',
        'descricao',
        'unidade_medida',
        'estoque_atual',
        'ativo',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function abrigo()
    {
        return $this->belongsTo(Abrigo::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoDonativo::class);
    }
}
