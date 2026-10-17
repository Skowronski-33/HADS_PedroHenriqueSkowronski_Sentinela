<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoaAbrigadaTransferencia extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'pessoa_abrigada_id',
        'abrigo_origem_id',
        'abrigo_destino_id',
        'user_id',
        'data_hora',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['data_hora' => 'datetime'];
    }

    public function pessoa()
    {
        return $this->belongsTo(PessoaAbrigada::class, 'pessoa_abrigada_id');
    }

    public function abrigoOrigem()
    {
        return $this->belongsTo(Abrigo::class, 'abrigo_origem_id');
    }

    public function abrigoDestino()
    {
        return $this->belongsTo(Abrigo::class, 'abrigo_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
