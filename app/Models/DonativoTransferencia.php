<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonativoTransferencia extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'donativo_origem_id',
        'donativo_destino_id',
        'quantidade',
        'user_id',
        'data_hora',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['data_hora' => 'datetime'];
    }

    public function donativoOrigem()
    {
        return $this->belongsTo(Donativo::class, 'donativo_origem_id');
    }

    public function donativoDestino()
    {
        return $this->belongsTo(Donativo::class, 'donativo_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
