<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEquipe extends Model
{
    public $timestamps = false;

    protected $table = 'tipos_equipe';

    protected $fillable = ['nome', 'descricao', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function equipes()
    {
        return $this->hasMany(Equipe::class);
    }
}
