<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipamento extends Model
{
    protected $fillable = [
        'tipo_equipamento_id',
        'nome',
        'status',
        'disponibilidade',
        'ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function tipoEquipamento()
    {
        return $this->belongsTo(TipoEquipamento::class);
    }

    public function atendimentos(): BelongsToMany
    {
        return $this->belongsToMany(Atendimento::class, 'atendimento_equipamento')
            ->withPivot('quantidade_utilizada');
    }
}
