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

    public function pessoasAbrigadas()
    {
        return $this->hasMany(PessoaAbrigada::class);
    }

    public function pessoasAtivas()
    {
        return $this->pessoasAbrigadas()->whereNull('data_saida');
    }

    public function ocupacaoAtual(): int
    {
        return $this->pessoasAtivas()->count();
    }
}
