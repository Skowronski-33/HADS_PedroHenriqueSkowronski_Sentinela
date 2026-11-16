<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'acao',
        'descricao',
        'subject_type',
        'subject_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Registra uma ação para fins de rastreabilidade/auditoria (RNF07).
     * Usuário é o autenticado no momento; se não houver (ex: comando artisan), fica nulo.
     */
    public static function registrar(string $acao, string $descricao, ?Model $subject = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'acao' => $acao,
            'descricao' => $descricao,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'created_at' => now(),
        ]);
    }
}
