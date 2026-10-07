<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abrigo extends Model
{
    protected $fillable = [
        'nome',
        'endereco',
        'bairro',
        'capacidade',
        'responsavel',
        'status',
        'ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function donativos()
    {
        return $this->hasMany(Donativo::class);
    }

    public function ocupacoes()
    {
        return $this->hasMany(OcupacaoAbrigo::class);
    }

    public function ocupacaoAtual(): int
    {
        return (int) $this->ocupacoes()
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo_movimentacao = 'entrada' THEN quantidade_pessoas ELSE -quantidade_pessoas END), 0) as total")
            ->value('total');
    }
}
