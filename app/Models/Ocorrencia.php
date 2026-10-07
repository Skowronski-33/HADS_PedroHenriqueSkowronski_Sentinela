<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ocorrencia extends Model
{
    protected $fillable = [
        'user_id',
        'tipo_ocorrencia_id',
        'prioridade_id',
        'descricao',
        'endereco',
        'bairro',
        'cidade',
        'ponto_referencia',
        'status',
        'data_hora',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['data_hora' => 'datetime'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoOcorrencia()
    {
        return $this->belongsTo(TipoOcorrencia::class);
    }

    public function prioridade()
    {
        return $this->belongsTo(Prioridade::class);
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class);
    }
}
