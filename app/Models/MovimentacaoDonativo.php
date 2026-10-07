<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimentacaoDonativo extends Model
{
    public $timestamps = false;

    protected $table = 'movimentacoes_donativos';

    protected $fillable = [
        'donativo_id',
        'user_id',
        'tipo_movimentacao',
        'quantidade',
        'data_hora',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['data_hora' => 'datetime'];
    }

    public function donativo()
    {
        return $this->belongsTo(Donativo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
