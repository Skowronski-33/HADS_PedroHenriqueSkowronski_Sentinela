<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaAbrigada extends Model
{
    protected $table = 'pessoas_abrigadas';

    protected $fillable = [
        'abrigo_id',
        'responsavel_familiar_id',
        'nome',
        'idade',
        'contato',
        'necessita_prioridade',
        'observacoes',
        'data_entrada',
        'data_saida',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'data_entrada' => 'datetime',
            'data_saida' => 'datetime',
            'necessita_prioridade' => 'boolean',
        ];
    }

    public function abrigo()
    {
        return $this->belongsTo(Abrigo::class);
    }

    public function responsavelFamiliar()
    {
        return $this->belongsTo(PessoaAbrigada::class, 'responsavel_familiar_id');
    }

    public function dependentes()
    {
        return $this->hasMany(PessoaAbrigada::class, 'responsavel_familiar_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function transferencias()
    {
        return $this->hasMany(PessoaAbrigadaTransferencia::class);
    }

    public function estaAbrigada(): bool
    {
        return $this->data_saida === null;
    }
}
