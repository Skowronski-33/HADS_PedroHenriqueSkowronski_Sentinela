<?php

namespace Database\Seeders;

use App\Models\Abrigo;
use App\Models\PessoaAbrigada;
use App\Models\User;
use Illuminate\Database\Seeder;

class PessoaAbrigadaSeeder extends Seeder
{
    public function run(): void
    {
        $registrador = User::where('email', 'admin@sentinela.local')->first();

        if (! $registrador) {
            $this->command->warn('Usuário admin não encontrado; pessoas abrigadas não foram criadas.');

            return;
        }

        $faker = fake('pt_BR');

        // [nome do abrigo => quantas pessoas deixar abrigadas no total]
        $ocupacaoAlvo = [
            'CGT Vento Minuano' => 14,
            'Escola Municipal São José' => 9,
            'Ginásio Municipal de Esportes' => 60, // bate a capacidade (60), reforça o status "Lotado"
            'Salão Paroquial Santo Antônio' => 7,
        ];

        // Famílias com nomes fixos, para o cenário ficar previsível ao testar manualmente.
        $familiasFixas = [
            'CGT Vento Minuano' => [
                ['responsavel' => ['nome' => 'Ana Paula Ribeiro', 'idade' => 38, 'contato' => '(54) 99111-0001'],
                    'dependentes' => [['nome' => 'Lucas Ribeiro', 'idade' => 9], ['nome' => 'Sofia Ribeiro', 'idade' => 5]]],
                ['responsavel' => ['nome' => 'Osvaldo Martins', 'idade' => 72, 'contato' => '(54) 99111-0002', 'necessita_prioridade' => true],
                    'dependentes' => []],
                ['responsavel' => ['nome' => 'Juliana Costa', 'idade' => 29, 'contato' => '(54) 99111-0003', 'necessita_prioridade' => true, 'observacoes' => 'Gestante, 7 meses.'],
                    'dependentes' => [['nome' => 'Pedro Costa', 'idade' => 3]]],
            ],
            'Escola Municipal São José' => [
                ['responsavel' => ['nome' => 'Fernando Dutra', 'idade' => 45, 'contato' => '(54) 99222-0001'],
                    'dependentes' => [['nome' => 'Camila Dutra', 'idade' => 17]]],
                ['responsavel' => ['nome' => 'Beatriz Nunes', 'idade' => 81, 'contato' => '(54) 99222-0002', 'necessita_prioridade' => true, 'observacoes' => 'Cadeirante.'],
                    'dependentes' => []],
            ],
            'Salão Paroquial Santo Antônio' => [
                ['responsavel' => ['nome' => 'Roberto Lima', 'idade' => 50, 'contato' => '(54) 99333-0001'],
                    'dependentes' => [['nome' => 'Henrique Lima', 'idade' => 12], ['nome' => 'Isabela Lima', 'idade' => 8]]],
            ],
        ];

        foreach ($ocupacaoAlvo as $nomeAbrigo => $totalDesejado) {
            $abrigo = Abrigo::where('nome', $nomeAbrigo)->first();

            if (! $abrigo) {
                continue;
            }

            // Idempotência simples: se o abrigo já tem gente abrigada, não duplica.
            if ($abrigo->ocupacaoAtual() > 0) {
                $this->command->info("\"{$nomeAbrigo}\" já tem pessoas abrigadas, pulando.");

                continue;
            }

            $criadas = 0;

            foreach ($familiasFixas[$nomeAbrigo] ?? [] as $familia) {
                $responsavel = PessoaAbrigada::create($familia['responsavel'] + [
                    'abrigo_id' => $abrigo->id,
                    'necessita_prioridade' => $familia['responsavel']['necessita_prioridade'] ?? false,
                    'data_entrada' => now()->subDays(random_int(1, 10)),
                    'registrado_por' => $registrador->id,
                ]);
                $criadas++;

                foreach ($familia['dependentes'] as $dependente) {
                    PessoaAbrigada::create($dependente + [
                        'abrigo_id' => $abrigo->id,
                        'responsavel_familiar_id' => $responsavel->id,
                        'necessita_prioridade' => false,
                        'data_entrada' => $responsavel->data_entrada,
                        'registrado_por' => $registrador->id,
                    ]);
                    $criadas++;
                }
            }

            // Completa até a ocupação alvo com pessoas avulsas geradas via Faker.
            while ($criadas < $totalDesejado) {
                PessoaAbrigada::create([
                    'abrigo_id' => $abrigo->id,
                    'nome' => $faker->name(),
                    'idade' => $faker->numberBetween(1, 85),
                    'contato' => $faker->boolean(60) ? $faker->numerify('(54) 9####-####') : null,
                    'necessita_prioridade' => $faker->boolean(10),
                    'data_entrada' => now()->subDays(random_int(1, 10)),
                    'registrado_por' => $registrador->id,
                ]);
                $criadas++;
            }
        }

        $this->command->info('Pessoas abrigadas de exemplo cadastradas.');
    }
}
