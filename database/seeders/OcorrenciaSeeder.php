<?php

namespace Database\Seeders;

use App\Models\Equipe;
use App\Models\Ocorrencia;
use App\Models\Prioridade;
use App\Models\TipoOcorrencia;
use App\Models\User;
use App\Models\Viatura;
use Illuminate\Database\Seeder;

class OcorrenciaSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@sentinela.local')->first();

        if (! $admin) {
            $this->command->warn('Usuário admin não encontrado — pulando seed de ocorrências.');

            return;
        }

        $aberta = Ocorrencia::firstOrCreate(
            ['endereco' => 'Rua das Palmeiras, 120', 'descricao' => 'Alagamento com risco a residências próximas ao córrego.'],
            [
                'user_id' => $admin->id,
                'tipo_ocorrencia_id' => TipoOcorrencia::where('nome', 'Enchente/Alagamento')->value('id'),
                'prioridade_id' => Prioridade::where('nome', 'Crítica')->value('id'),
                'bairro' => 'Vila Esperança',
                'cidade' => 'São Leopoldo',
                'status' => 'Aberta',
                'data_hora' => now()->subHours(2),
            ]
        );

        $emAtendimento = Ocorrencia::firstOrCreate(
            ['endereco' => 'Av. Dois Irmãos, 450', 'descricao' => 'Deslizamento de terra atingindo parte do talude da via.'],
            [
                'user_id' => $admin->id,
                'tipo_ocorrencia_id' => TipoOcorrencia::where('nome', 'Deslizamento')->value('id'),
                'prioridade_id' => Prioridade::where('nome', 'Alta')->value('id'),
                'bairro' => 'Morro do Espelho',
                'cidade' => 'São Leopoldo',
                'status' => 'Em Atendimento',
                'data_hora' => now()->subHours(5),
            ]
        );

        if ($emAtendimento->atendimentos()->count() === 0) {
            $equipeCharlie = Equipe::where('nome', 'Charlie')->first();
            $viaturaResgate = Viatura::where('placa', 'ABC2D34')->first();

            if ($equipeCharlie && $viaturaResgate) {
                $emAtendimento->atendimentos()->create([
                    'equipe_id' => $equipeCharlie->id,
                    'viatura_id' => $viaturaResgate->id,
                    'data_hora_saida' => now()->subHours(4),
                    'status' => 'No Local',
                ]);
            }
        }

        $encerrada = Ocorrencia::firstOrCreate(
            ['endereco' => 'Rua Marechal Deodoro, 980', 'descricao' => 'Princípio de incêndio em estabelecimento comercial, controlado sem vítimas.'],
            [
                'user_id' => $admin->id,
                'tipo_ocorrencia_id' => TipoOcorrencia::where('nome', 'Incêndio')->value('id'),
                'prioridade_id' => Prioridade::where('nome', 'Média')->value('id'),
                'bairro' => 'Centro',
                'cidade' => 'São Leopoldo',
                'status' => 'Encerrada',
                'data_hora' => now()->subDay(),
            ]
        );

        if ($encerrada->atendimentos()->count() === 0) {
            $equipeAlfa = Equipe::where('nome', 'Alfa')->first();

            if ($equipeAlfa) {
                $encerrada->atendimentos()->create([
                    'equipe_id' => $equipeAlfa->id,
                    'data_hora_saida' => now()->subDay()->addMinutes(10),
                    'data_hora_retorno' => now()->subDay()->addHours(2),
                    'status' => 'Concluído',
                ]);
            }
        }

        $this->command->info('Ocorrências de exemplo cadastradas.');
    }
}
