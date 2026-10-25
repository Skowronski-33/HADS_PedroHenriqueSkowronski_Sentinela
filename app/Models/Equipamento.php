<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Equipamento extends Model
{
    protected $fillable = [
        'tipo_equipamento_id',
        'nome',
        'patrimonio',
        'localizacao',
        'data_ultima_manutencao',
        'status',
        'disponibilidade',
        'ativo',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'data_ultima_manutencao' => 'date',
        ];
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

    public function iconeTipo(): string
    {
        $tipo = mb_strtolower($this->tipoEquipamento->nome ?? '');

        return match (true) {
            str_contains($tipo, 'drone') || str_contains($tipo, 'aére') || str_contains($tipo, 'aere') => 'bi-airplane',
            str_contains($tipo, 'gerador') || str_contains($tipo, 'energia') => 'bi-lightning-charge-fill',
            str_contains($tipo, 'comunica') || str_contains($tipo, 'rádio') || str_contains($tipo, 'radio') => 'bi-broadcast',
            str_contains($tipo, 'resgate') => 'bi-life-preserver',
            str_contains($tipo, 'socorro') || str_contains($tipo, 'médic') || str_contains($tipo, 'medic') => 'bi-bandaid-fill',
            str_contains($tipo, 'embarca') || str_contains($tipo, 'barco') || str_contains($tipo, 'bote') => 'bi-compass-fill',
            str_contains($tipo, 'ilumina') || str_contains($tipo, 'lanterna') => 'bi-flashlight',
            str_contains($tipo, 'corte') || str_contains($tipo, 'ferramenta') => 'bi-wrench-adjustable',
            default => 'bi-tools',
        };
    }
}
