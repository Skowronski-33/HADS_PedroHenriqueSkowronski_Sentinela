<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoOcorrencia extends Model
{
    public $timestamps = false;

    protected $table = 'tipos_ocorrencia';

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
