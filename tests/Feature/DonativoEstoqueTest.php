<?php

namespace Tests\Feature;

use App\Models\Abrigo;
use App\Models\Donativo;
use Tests\TestCase;

class DonativoEstoqueTest extends TestCase
{
    private function criarAbrigo(string $nome = 'Abrigo Central'): Abrigo
    {
        return Abrigo::create([
            'nome' => $nome,
            'endereco' => 'Av. Principal, 100',
            'capacidade' => 50,
            'status' => 'Ativo',
        ]);
    }

    public function test_entrada_e_saida_atualizam_estoque_dentro_do_limite(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $abrigo = $this->criarAbrigo();
        $donativo = $abrigo->donativos()->create([
            'tipo' => 'Água mineral',
            'unidade_medida' => 'Litros',
            'estoque_atual' => 0,
            'estoque_minimo' => 20,
        ]);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.donativos.entrada', [$abrigo, $donativo]),
            ['quantidade' => 50]
        )->assertRedirect(route('abrigos.donativos.index', $abrigo));

        $this->assertSame(50, $donativo->refresh()->estoque_atual);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.donativos.saida', [$abrigo, $donativo]),
            ['quantidade' => 30]
        )->assertRedirect(route('abrigos.donativos.index', $abrigo));

        $this->assertSame(20, $donativo->refresh()->estoque_atual);
    }

    public function test_saida_e_bloqueada_quando_quantidade_maior_que_estoque(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $abrigo = $this->criarAbrigo();
        $donativo = $abrigo->donativos()->create([
            'tipo' => 'Cesta básica',
            'unidade_medida' => 'Cestas Básicas',
            'estoque_atual' => 5,
            'estoque_minimo' => 10,
        ]);

        $response = $this->actingAs($gerenciador)->patch(
            route('abrigos.donativos.saida', [$abrigo, $donativo]),
            ['quantidade' => 10]
        );

        $response->assertSessionHas('error');
        $this->assertSame(5, $donativo->refresh()->estoque_atual);
    }

    public function test_transferencia_move_estoque_entre_abrigos(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $origem = $this->criarAbrigo('Abrigo Central');
        $destino = $this->criarAbrigo('Abrigo Norte');

        $donativo = $origem->donativos()->create([
            'tipo' => 'Cobertor',
            'unidade_medida' => 'Unidades',
            'estoque_atual' => 40,
            'estoque_minimo' => 10,
        ]);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.donativos.transferir', [$origem, $donativo]),
            ['abrigo_destino_id' => $destino->id, 'quantidade' => 15]
        )->assertRedirect(route('abrigos.donativos.index', $origem));

        $this->assertSame(25, $donativo->refresh()->estoque_atual);
        $this->assertSame(15, $destino->donativos()->where('tipo', 'Cobertor')->first()->estoque_atual);
    }

    public function test_estoque_minimo_padrao_e_dez_quando_nao_informado(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $abrigo = $this->criarAbrigo();

        $this->actingAs($gerenciador)->post(route('abrigos.donativos.store', $abrigo), [
            'tipo' => 'Leite em pó',
            'unidade_medida' => 'Quilogramas (kg)',
        ])->assertRedirect(route('abrigos.donativos.index', $abrigo));

        $this->assertSame(10, Donativo::where('tipo', 'Leite em pó')->first()->estoque_minimo);
    }
}
