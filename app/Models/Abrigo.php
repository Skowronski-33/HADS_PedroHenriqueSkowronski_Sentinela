<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abrigo extends Model
{
    protected $fillable = [
        'nome',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'capacidade',
        'responsavel',
        'telefone_responsavel',
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

    public function enderecoCompleto(): string
    {
        $partes = [
            trim($this->endereco.($this->numero ? ', '.$this->numero : '')),
            $this->bairro,
            $this->cidade,
        ];

        return implode(' - ', array_filter($partes));
    }
}
