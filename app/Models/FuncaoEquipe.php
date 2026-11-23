<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuncaoEquipe extends Model
{
    public $timestamps = false;

    protected $table = 'funcoes_equipe';

    protected $fillable = ['nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }
}
