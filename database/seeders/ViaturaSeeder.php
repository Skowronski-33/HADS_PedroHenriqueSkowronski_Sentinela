<?php

namespace Database\Seeders;

use App\Models\Viatura;
use Illuminate\Database\Seeder;

class ViaturaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['placa' => 'ABC1D23', 'identificacao' => 'Viatura 01', 'tipo' => 'Comando', 'modelo' => 'Toyota Hilux', 'status' => 'Disponível'],
            ['placa' => 'ABC2D34', 'identificacao' => 'Viatura 02', 'tipo' => 'Resgate', 'modelo' => 'Mercedes-Benz Sprinter', 'status' => 'Em Atendimento'],
            ['placa' => 'ABC3D45', 'identificacao' => 'Viatura 03', 'tipo' => 'Ambulância', 'modelo' => 'Fiat Ducato', 'status' => 'Disponível'],
            ['placa' => 'ABC4D56', 'identificacao' => 'Viatura 04', 'tipo' => 'Caminhão-Pipa', 'modelo' => 'Volkswagen Constellation', 'status' => 'Em Manutenção'],
            ['placa' => 'ABC5D67', 'identificacao' => 'Viatura 05', 'tipo' => 'Apoio 4x4', 'modelo' => 'Ford Ranger', 'status' => 'Disponível'],
            ['placa' => 'ABC6D78', 'identificacao' => 'Viatura 06', 'tipo' => 'Transporte', 'modelo' => 'Mercedes-Benz Sprinter', 'status' => 'Disponível'],
        ] as $viatura) {
            Viatura::firstOrCreate(
                ['placa' => $viatura['placa']],
                [
                    'identificacao' => $viatura['identificacao'],
                    'tipo' => $viatura['tipo'],
                    'modelo' => $viatura['modelo'],
                    'status' => $viatura['status'],
                    'disponibilidade' => $viatura['status'] === 'Disponível' ? 'Sim' : 'Não',
                    'ativo' => true,
                ]
            );
        }

        $this->command->info('Viaturas de exemplo cadastradas.');
    }
}
