<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// RNF10: backup periódico da base de dados. Isso só dispara de fato se algo
// chamar `php artisan schedule:run` a cada minuto (cron no Linux, Agendador
// de Tarefas no Windows) — o Laravel não se auto-agenda sozinho.
Schedule::command('backup:database')->daily()->at('02:00')
    ->onSuccess(fn () => Log::info('Backup periódico do banco concluído com sucesso.'))
    ->onFailure(fn () => Log::error('Backup periódico do banco falhou.'));
