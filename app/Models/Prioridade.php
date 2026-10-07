<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prioridade extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome', 'descricao', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function ocorrencias()
    {
        return $this->hasMany(Ocorrencia::class);
    }
}
