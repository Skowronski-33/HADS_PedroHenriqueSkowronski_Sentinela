<?php

namespace Database\Seeders;

use App\Models\Equipamento;
use App\Models\TipoEquipamento;
use Illuminate\Database\Seeder;

class EquipamentoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nome' => 'Bote Inflável Zodiac', 'tipo' => 'Embarcação', 'patrimonio' => 'PAT-0001', 'localizacao' => 'Depósito Central', 'status' => 'Disponível'],
            ['nome' => 'Corda de Resgate 50m', 'tipo' => 'Equipamento de Resgate', 'patrimonio' => 'PAT-0002', 'localizacao' => 'Depósito Central', 'status' => 'Disponível'],
            ['nome' => 'Rádio HT Motorola', 'tipo' => 'Equipamento de Comunicação', 'patrimonio' => 'PAT-0003', 'localizacao' => 'Base Operacional', 'status' => 'Em Uso'],
            ['nome' => 'Rádio HT Motorola #2', 'tipo' => 'Equipamento de Comunicação', 'patrimonio' => 'PAT-0004', 'localizacao' => 'Base Operacional', 'status' => 'Disponível'],
            ['nome' => 'Gerador Honda EG5000', 'tipo' => 'Gerador', 'patrimonio' => 'PAT-0005', 'localizacao' => 'Depósito Central', 'status' => 'Em Manutenção'],
            ['nome' => 'Kit de Primeiros Socorros #1', 'tipo' => 'Primeiros Socorros', 'patrimonio' => 'PAT-0006', 'localizacao' => 'Abrigo Municipal', 'status' => 'Disponível'],
            ['nome' => 'Kit de Primeiros Socorros #2', 'tipo' => 'Primeiros Socorros', 'patrimonio' => 'PAT-0007', 'localizacao' => 'Base Operacional', 'status' => 'Indisponível'],
            ['nome' => 'Colete Salva-Vidas (par)', 'tipo' => 'Equipamento de Resgate', 'patrimonio' => 'PAT-0008', 'localizacao' => 'Depósito Central', 'status' => 'Disponível'],
        ] as $equipamento) {
            $tipo = TipoEquipamento::where('nome', $equipamento['tipo'])->first();

            Equipamento::firstOrCreate(
                ['nome' => $equipamento['nome']],
                [
                    'tipo_equipamento_id' => $tipo->id,
                    'patrimonio' => $equipamento['patrimonio'],
                    'localizacao' => $equipamento['localizacao'],
                    'status' => $equipamento['status'],
                    'disponibilidade' => $equipamento['status'] === 'Disponível' ? 'Sim' : 'Não',
                    'ativo' => true,
                ]
            );
        }

        $this->command->info('Equipamentos de exemplo cadastrados.');
    }
}
