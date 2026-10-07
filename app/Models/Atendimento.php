<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Atendimento extends Model
{
    protected $fillable = [
        'ocorrencia_id',
        'equipe_id',
        'viatura_id',
        'data_hora_saida',
        'data_hora_retorno',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_hora_saida' => 'datetime',
            'data_hora_retorno' => 'datetime',
        ];
    }

    public function ocorrencia()
    {
        return $this->belongsTo(Ocorrencia::class);
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    public function viatura()
    {
        return $this->belongsTo(Viatura::class);
    }

    public function equipamentos(): BelongsToMany
    {
        return $this->belongsToMany(Equipamento::class, 'atendimento_equipamento')
            ->withPivot('quantidade_utilizada');
    }
}
