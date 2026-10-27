<?php

namespace Database\Seeders;

use App\Models\Voluntario;
use Illuminate\Database\Seeder;

class VoluntarioSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nome' => 'Ana Carolina Ferreira', 'telefone' => '(54) 99101-2233', 'habilidade' => 'Primeiros socorros', 'disponibilidade' => 'Disponível', 'ativo' => true],
            ['nome' => 'Bruno Costa Lima', 'telefone' => '(54) 99202-3344', 'habilidade' => 'Condução de veículos', 'disponibilidade' => 'Disponível', 'ativo' => true],
            ['nome' => 'Camila Rodrigues Souza', 'telefone' => '(54) 99303-4455', 'habilidade' => 'Resgate aquático', 'disponibilidade' => 'Indisponível', 'ativo' => true],
            ['nome' => 'Diego Martins Oliveira', 'telefone' => '(54) 99404-5566', 'habilidade' => 'Comunicação por rádio', 'disponibilidade' => 'Disponível', 'ativo' => true],
            ['nome' => 'Eduarda Santos Pereira', 'telefone' => '(54) 99505-6677', 'habilidade' => 'Apoio logístico', 'disponibilidade' => 'Disponível', 'ativo' => true],
            ['nome' => 'Felipe Almeida Barros', 'telefone' => '(54) 99606-7788', 'habilidade' => null, 'disponibilidade' => 'Disponível', 'ativo' => true],
            ['nome' => 'Gabriela Nunes Teixeira', 'telefone' => '(54) 99707-8899', 'habilidade' => 'Culinária em massa', 'disponibilidade' => 'Indisponível', 'ativo' => true],
            ['nome' => 'Henrique Ribeiro Dias', 'telefone' => '(54) 99808-9900', 'habilidade' => 'Primeiros socorros', 'disponibilidade' => 'Indisponível', 'ativo' => false],
        ] as $voluntario) {
            Voluntario::firstOrCreate(['nome' => $voluntario['nome']], $voluntario);
        }

        $this->command->info('Voluntários de exemplo cadastrados.');
    }
}
