<?php

namespace Database\Seeders;

use App\Models\Prioridade;
use App\Models\TipoEquipamento;
use App\Models\TipoEquipe;
use App\Models\TipoOcorrencia;
use Illuminate\Database\Seeder;

class LookupDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Enchente/Alagamento',
            'Deslizamento',
            'Incêndio',
            'Resgate/Salvamento',
            'Atendimento Médico',
            'Risco Estrutural',
            'Outros',
        ] as $nome) {
            TipoOcorrencia::firstOrCreate(['nome' => $nome]);
        }

        foreach ([
            'Baixa' => 'Sem risco iminente',
            'Média' => 'Requer atenção em breve',
            'Alta' => 'Requer atendimento rápido',
            'Crítica' => 'Risco iminente à vida',
        ] as $nome => $descricao) {
            Prioridade::firstOrCreate(['nome' => $nome], ['descricao' => $descricao]);
        }

        foreach ([
            'Bombeiros',
            'Defesa Civil',
            'Resgate Aquático',
            'Apoio Logístico',
        ] as $nome) {
            TipoEquipe::firstOrCreate(['nome' => $nome]);
        }

        foreach ([
            'Embarcação',
            'Equipamento de Resgate',
            'Equipamento de Comunicação',
            'Gerador',
            'Kit de Primeiros Socorros',
        ] as $nome) {
            TipoEquipamento::firstOrCreate(['nome' => $nome]);
        }

        $this->command->info('Dados de referência (tipos e prioridades) cadastrados.');
    }
}
