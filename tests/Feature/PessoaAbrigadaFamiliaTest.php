<?php

namespace Tests\Feature;

use App\Models\Abrigo;
use App\Models\User;
use Tests\TestCase;

class PessoaAbrigadaFamiliaTest extends TestCase
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

    private function criarFamilia(Abrigo $abrigo, User $registrante): array
    {
        $responsavel = $abrigo->pessoasAbrigadas()->create([
            'nome' => 'Maria Responsável',
            'data_entrada' => now(),
            'registrado_por' => $registrante->id,
        ]);

        $dependente = $abrigo->pessoasAbrigadas()->create([
            'nome' => 'João Dependente',
            'responsavel_familiar_id' => $responsavel->id,
            'data_entrada' => now(),
            'registrado_por' => $registrante->id,
        ]);

        return [$responsavel, $dependente];
    }

    public function test_saida_do_responsavel_leva_dependente_junto(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $abrigo = $this->criarAbrigo();
        [$responsavel, $dependente] = $this->criarFamilia($abrigo, $gerenciador);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.pessoas.saida', [$abrigo, $responsavel])
        )->assertRedirect(route('abrigos.show', $abrigo));

        $this->assertNotNull($responsavel->refresh()->data_saida);
        $this->assertNotNull($dependente->refresh()->data_saida);
    }

    public function test_transferir_dependente_leva_a_familia_toda(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $origem = $this->criarAbrigo('Abrigo Central');
        $destino = $this->criarAbrigo('Abrigo Norte');
        [$responsavel, $dependente] = $this->criarFamilia($origem, $gerenciador);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.pessoas.transferir', [$origem, $dependente]),
            ['abrigo_destino_id' => $destino->id]
        )->assertRedirect(route('abrigos.show', $origem));

        $this->assertSame($destino->id, $responsavel->refresh()->abrigo_id);
        $this->assertSame($destino->id, $dependente->refresh()->abrigo_id);
    }

    public function test_transferir_dependente_sozinho_sem_a_familia(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $origem = $this->criarAbrigo('Abrigo Central');
        $destino = $this->criarAbrigo('Abrigo Norte');
        [$responsavel, $dependente] = $this->criarFamilia($origem, $gerenciador);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.pessoas.transferir', [$origem, $dependente]),
            ['abrigo_destino_id' => $destino->id, 'incluir_familia' => '0']
        )->assertRedirect(route('abrigos.show', $origem));

        $this->assertSame($destino->id, $dependente->refresh()->abrigo_id);
        $this->assertNull($dependente->responsavel_familiar_id);
        $this->assertSame($origem->id, $responsavel->refresh()->abrigo_id);
    }

    public function test_transferir_responsavel_sozinho_deixa_dependente_independente(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $origem = $this->criarAbrigo('Abrigo Central');
        $destino = $this->criarAbrigo('Abrigo Norte');
        [$responsavel, $dependente] = $this->criarFamilia($origem, $gerenciador);

        $this->actingAs($gerenciador)->patch(
            route('abrigos.pessoas.transferir', [$origem, $responsavel]),
            ['abrigo_destino_id' => $destino->id, 'incluir_familia' => '0']
        )->assertRedirect(route('abrigos.show', $origem));

        $this->assertSame($destino->id, $responsavel->refresh()->abrigo_id);
        $this->assertSame($origem->id, $dependente->refresh()->abrigo_id);
        $this->assertNull($dependente->responsavel_familiar_id);
    }

    public function test_edicao_nao_permite_criar_ciclo_familiar(): void
    {
        $gerenciador = $this->criarUsuarioComPerfil('Gerenciador de Abrigo');
        $abrigo = $this->criarAbrigo();
        [$responsavel, $dependente] = $this->criarFamilia($abrigo, $gerenciador);

        $response = $this->actingAs($gerenciador)->patch(
            route('abrigos.pessoas.update', [$abrigo, $responsavel]),
            [
                'nome' => $responsavel->nome,
                'responsavel_familiar_id' => $dependente->id,
            ]
        );

        $response->assertSessionHasErrors('responsavel_familiar_id', null, 'pessoa_edit');
        $this->assertNull($responsavel->refresh()->responsavel_familiar_id);
    }
}
