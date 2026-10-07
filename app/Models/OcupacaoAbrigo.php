<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OcupacaoAbrigo extends Model
{
    public $timestamps = false;

    protected $table = 'ocupacoes_abrigos';

    protected $fillable = [
        'abrigo_id',
        'user_id',
        'tipo_movimentacao',
        'quantidade_pessoas',
        'data_hora',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['data_hora' => 'datetime'];
    }

    public function abrigo()
    {
        return $this->belongsTo(Abrigo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
