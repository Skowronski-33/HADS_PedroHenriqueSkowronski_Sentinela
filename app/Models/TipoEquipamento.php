<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipamento extends Model
{
    public $timestamps = false;

    protected $table = 'tipos_equipamento';

    protected $fillable = ['nome', 'descricao', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class);
    }
}
