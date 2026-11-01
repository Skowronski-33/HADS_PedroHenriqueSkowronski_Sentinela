<?php

namespace Database\Seeders;

use App\Models\Equipe;
use App\Models\TipoEquipe;
use App\Models\Voluntario;
use Illuminate\Database\Seeder;

class EquipeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nome' => 'Alfa', 'tipo' => 'Bombeiros', 'status' => 'Disponível', 'membros' => ['Ana Carolina Ferreira', 'Henrique Ribeiro Dias']],
            ['nome' => 'Bravo', 'tipo' => 'Resgate Aquático', 'status' => 'Disponível', 'membros' => ['Camila Rodrigues Souza', 'Bruno Costa Lima']],
            ['nome' => 'Charlie', 'tipo' => 'Defesa Civil', 'status' => 'Em Serviço', 'membros' => ['Diego Martins Oliveira', 'Eduarda Santos Pereira']],
            ['nome' => 'Delta', 'tipo' => 'Apoio Logístico', 'status' => 'Disponível', 'membros' => ['Felipe Almeida Barros', 'Gabriela Nunes Teixeira']],
        ] as $dados) {
            $tipo = TipoEquipe::where('nome', $dados['tipo'])->first();

            $equipe = Equipe::firstOrCreate(
                ['nome' => $dados['nome']],
                [
                    'tipo_equipe_id' => $tipo?->id,
                    'status' => $dados['status'],
                    'ativo' => true,
                ]
            );

            $membrosIds = Voluntario::whereIn('nome', $dados['membros'])->pluck('id');
            $equipe->voluntarios()->syncWithoutDetaching($membrosIds);
        }

        $this->command->info('Equipes de exemplo cadastradas.');
    }
}
