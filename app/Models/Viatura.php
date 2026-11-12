<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viatura extends Model
{
    protected $fillable = [
        'placa',
        'identificacao',
        'tipo',
        'modelo',
        'status',
        'disponibilidade',
        'ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class);
    }

    public function iconeTipo(): string
    {
        return match ($this->tipo) {
            'Comando' => 'bi-broadcast-pin',
            'Resgate' => 'bi-life-preserver',
            'Ambulância' => 'bi-bandaid-fill',
            'Caminhão-Pipa' => 'bi-droplet-fill',
            'Apoio 4x4' => 'bi-signpost-2-fill',
            'Transporte' => 'bi-bus-front-fill',
            default => 'bi-truck',
        };
    }

    /**
     * Cor do badge de tipo (tag-chip-* do sentinela.css), para diferenciar
     * rapidamente o tipo de viatura no card sem poluir o layout.
     */
    public function corTipo(): string
    {
        return match ($this->tipo) {
            'Comando' => 'blue',
            'Resgate' => 'teal',
            'Ambulância' => 'red',
            'Caminhão-Pipa' => 'green',
            'Apoio 4x4' => 'amber',
            'Transporte' => 'purple',
            default => 'slate',
        };
    }
}
