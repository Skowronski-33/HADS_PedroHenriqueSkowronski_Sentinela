<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--manter=10 : Quantos backups mais recentes manter; os mais antigos são apagados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera um dump da base de dados (RNF10) e remove backups antigos além do limite configurado';

    public function handle(): int
    {
        $conexao = config('database.default');
        $config = config("database.connections.{$conexao}");

        if (($config['driver'] ?? null) !== 'pgsql') {
            $this->error('Este comando só sabe gerar backup de uma conexão PostgreSQL (pgsql).');

            return self::FAILURE;
        }

        $diretorio = storage_path('app/backups');

        if (! is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $arquivo = $diretorio.DIRECTORY_SEPARATOR.sprintf(
            '%s_%s.sql',
            $config['database'],
            now()->format('Y-m-d_His')
        );

        $processo = new Process([
            'pg_dump',
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--username='.$config['username'],
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            '--file='.$arquivo,
            $config['database'],
        ]);

        $processo->setEnv(['PGPASSWORD' => $config['password']]);
        $processo->setTimeout(300);
        $processo->run();

        if (! $processo->isSuccessful()) {
            $this->error('Falha ao gerar o backup: '.$processo->getErrorOutput());

            return self::FAILURE;
        }

        $tamanhoKb = round(filesize($arquivo) / 1024, 1);
        $this->info("Backup gerado: {$arquivo} ({$tamanhoKb} KB)");

        $this->removerBackupsAntigos($diretorio, (int) $this->option('manter'));

        return self::SUCCESS;
    }

    private function removerBackupsAntigos(string $diretorio, int $manter): void
    {
        $arquivos = collect(glob($diretorio.DIRECTORY_SEPARATOR.'*.sql'))
            ->sortByDesc(fn (string $caminho) => filemtime($caminho))
            ->values();

        $arquivos->slice($manter)->each(function (string $caminho) {
            unlink($caminho);
            $this->line('Backup antigo removido: '.basename($caminho));
        });
    }
}
