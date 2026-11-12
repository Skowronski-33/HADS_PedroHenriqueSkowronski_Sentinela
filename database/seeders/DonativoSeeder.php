<?php

namespace Database\Seeders;

use App\Models\Abrigo;
use App\Models\Donativo;
use App\Models\MovimentacaoDonativo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DonativoSeeder extends Seeder
{
    public function run(): void
    {
        $registrador = User::where('email', 'admin@sentinela.local')->first();

        $itensPorAbrigo = [
            'CGT Vento Minuano' => [
                ['tipo' => 'Água Mineral', 'descricao' => 'Garrafas de 500ml', 'unidade_medida' => 'Unidades', 'estoque' => 1200],
                ['tipo' => 'Cesta Básica', 'descricao' => null, 'unidade_medida' => 'Cestas Básicas', 'estoque' => 180],
                ['tipo' => 'Cobertor', 'descricao' => 'Cobertor casal', 'unidade_medida' => 'Unidades', 'estoque' => 90],
                ['tipo' => 'Roupas de Inverno', 'descricao' => 'Doações variadas', 'unidade_medida' => 'Pacotes', 'estoque' => 45],
                ['tipo' => 'Produtos de Higiene', 'descricao' => 'Kits individuais', 'unidade_medida' => 'Conjuntos', 'estoque' => 150],
            ],
            'Escola Municipal São José' => [
                ['tipo' => 'Água Mineral', 'descricao' => 'Garrafas de 1,5L', 'unidade_medida' => 'Unidades', 'estoque' => 300],
                ['tipo' => 'Arroz', 'descricao' => 'Pacote 5kg', 'unidade_medida' => 'Quilogramas (kg)', 'estoque' => 80],
                ['tipo' => 'Fraldas Infantis', 'descricao' => 'Tamanho M', 'unidade_medida' => 'Pacotes', 'estoque' => 25],
                ['tipo' => 'Cobertor', 'descricao' => null, 'unidade_medida' => 'Unidades', 'estoque' => 40],
            ],
            'Ginásio Municipal de Esportes' => [
                ['tipo' => 'Água Mineral', 'descricao' => 'Garrafas de 500ml', 'unidade_medida' => 'Unidades', 'estoque' => 60],
                ['tipo' => 'Cesta Básica', 'descricao' => null, 'unidade_medida' => 'Cestas Básicas', 'estoque' => 10],
                ['tipo' => 'Calçados', 'descricao' => 'Numerações variadas', 'unidade_medida' => 'Pares', 'estoque' => 18],
            ],
            'Salão Paroquial Santo Antônio' => [
                ['tipo' => 'Água Mineral', 'descricao' => 'Garrafas de 1,5L', 'unidade_medida' => 'Unidades', 'estoque' => 120],
                ['tipo' => 'Kit de Limpeza', 'descricao' => 'Álcool, sabão e desinfetante', 'unidade_medida' => 'Conjuntos', 'estoque' => 20],
                ['tipo' => 'Leite em Pó', 'descricao' => 'Lata 400g', 'unidade_medida' => 'Unidades', 'estoque' => 35],
            ],
        ];

        foreach ($itensPorAbrigo as $nomeAbrigo => $itens) {
            $abrigo = Abrigo::where('nome', $nomeAbrigo)->first();

            if (! $abrigo) {
                continue;
            }

            foreach ($itens as $item) {
                $donativo = Donativo::firstOrCreate(
                    ['abrigo_id' => $abrigo->id, 'tipo' => $item['tipo']],
                    [
                        'descricao' => $item['descricao'],
                        'unidade_medida' => $item['unidade_medida'],
                        'estoque_atual' => 0,
                        'ativo' => true,
                    ]
                );

                if ($donativo->wasRecentlyCreated && $item['estoque'] > 0) {
                    $donativo->update(['estoque_atual' => $item['estoque']]);

                    if ($registrador) {
                        MovimentacaoDonativo::create([
                            'donativo_id' => $donativo->id,
                            'user_id' => $registrador->id,
                            'tipo_movimentacao' => 'entrada',
                            'quantidade' => $item['estoque'],
                            'data_hora' => now(),
                            'observacoes' => 'Estoque inicial (carga de teste).',
                        ]);
                    }
                }
            }
        }

        $this->command->info('Donativos de exemplo cadastrados.');
    }
}
