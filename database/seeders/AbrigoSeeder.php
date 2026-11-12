<?php

namespace Database\Seeders;

use App\Models\Abrigo;
use Illuminate\Database\Seeder;

class AbrigoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'nome' => 'CGT Vento Minuano',
                'endereco' => 'Rua Alexandre da Motta', 'numero' => '132', 'bairro' => 'Centro', 'cidade' => 'Carazinho',
                'capacidade' => 800, 'responsavel' => 'Marcos Vinícius', 'telefone_responsavel' => '(54) 99911-2233',
                'status' => 'Ativo',
            ],
            [
                'nome' => 'Escola Municipal São José',
                'endereco' => 'Av. Flores da Cunha', 'numero' => '850', 'bairro' => 'São José', 'cidade' => 'Carazinho',
                'capacidade' => 150, 'responsavel' => 'Lúcia Ferreira', 'telefone_responsavel' => '(54) 99822-4455',
                'status' => 'Ativo',
            ],
            [
                'nome' => 'Ginásio Municipal de Esportes',
                'endereco' => 'Rua Pernambuco', 'numero' => '45', 'bairro' => 'Humaitá', 'cidade' => 'Carazinho',
                'capacidade' => 60, 'responsavel' => 'João Pedro Alves', 'telefone_responsavel' => '(54) 99733-6677',
                'status' => 'Lotado',
            ],
            [
                'nome' => 'Salão Paroquial Santo Antônio',
                'endereco' => 'Rua Santo Antônio', 'numero' => '210', 'bairro' => 'Santo Antônio', 'cidade' => 'Carazinho',
                'capacidade' => 40, 'responsavel' => 'Mariana Souza', 'telefone_responsavel' => '(54) 99644-8899',
                'status' => 'Ativo',
            ],
            [
                'nome' => 'Centro Comunitário Bairro Verde',
                'endereco' => 'Travessa das Acácias', 'numero' => '30', 'bairro' => 'Bairro Verde', 'cidade' => 'Carazinho',
                'capacidade' => 25, 'responsavel' => 'Carlos Eduardo', 'telefone_responsavel' => '(54) 99555-1122',
                'status' => 'Inativo',
            ],
        ] as $abrigo) {
            Abrigo::firstOrCreate(
                ['nome' => $abrigo['nome']],
                [
                    'endereco' => $abrigo['endereco'],
                    'numero' => $abrigo['numero'],
                    'bairro' => $abrigo['bairro'],
                    'cidade' => $abrigo['cidade'],
                    'capacidade' => $abrigo['capacidade'],
                    'responsavel' => $abrigo['responsavel'],
                    'telefone_responsavel' => $abrigo['telefone_responsavel'],
                    'status' => $abrigo['status'],
                    'ativo' => $abrigo['status'] !== 'Inativo',
                ]
            );
        }

        $this->command->info('Abrigos de exemplo cadastrados.');
    }
}
