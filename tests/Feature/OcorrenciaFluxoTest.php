<?php

namespace Tests\Feature;

use App\Models\Equipamento;
use App\Models\Equipe;
use App\Models\Ocorrencia;
use App\Models\Prioridade;
use App\Models\TipoEquipamento;
use App\Models\TipoEquipe;
use App\Models\TipoOcorrencia;
use App\Models\Viatura;
use Tests\TestCase;

class OcorrenciaFluxoTest extends TestCase
{
    private function criarOcorrenciaAberta(): Ocorrencia
    {
        $atendente = $this->criarUsuarioComPerfil('Atendente de Ocorrências');

        return Ocorrencia::create([
            'user_id' => $atendente->id,
            'tipo_ocorrencia_id' => TipoOcorrencia::first()->id,
            'descricao' => 'Família ilhada pela enchente.',
            'endereco' => 'Rua das Flores',
            'bairro' => 'Centro',
            'status' => 'Aberta',
            'data_hora' => now(),
        ]);
    }

    public function test_ocorrencia_so_pode_ser_despachada_apos_triagem(): void
    {
        $gerente = $this->criarUsuarioComPerfil('Gerente Operacional');
        $ocorrencia = $this->criarOcorrenciaAberta();
        $equipe = Equipe::create([
            'tipo_equipe_id' => TipoEquipe::first()->id,
            'nome' => 'Equipe Alfa',
            'status' => 'Disponível',
        ]);

        $response = $this->actingAs($gerente)->post(
            route('ocorrencias.atendimentos.store', $ocorrencia),
            ['equipe_id' => $equipe->id]
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('atendimentos', 0);
    }

    public function test_triagem_libera_despacho_e_trava_equipamento_em_uso(): void
    {
        $atendente = $this->criarUsuarioComPerfil('Atendente de Ocorrências');
        $gerente = $this->criarUsuarioComPerfil('Gerente Operacional');
        $ocorrencia = $this->criarOcorrenciaAberta();

        $this->actingAs($atendente)->patch(route('ocorrencias.triar', $ocorrencia), [
            'tipo_ocorrencia_id' => $ocorrencia->tipo_ocorrencia_id,
            'prioridade_id' => Prioridade::where('nome', 'Alta')->first()->id,
        ])->assertRedirect(route('ocorrencias.show', $ocorrencia));

        $ocorrencia->refresh();
        $this->assertSame('Triada', $ocorrencia->status);

        $equipe = Equipe::create([
            'tipo_equipe_id' => TipoEquipe::first()->id,
            'nome' => 'Equipe Alfa',
            'status' => 'Disponível',
        ]);
        $viatura = Viatura::create([
            'placa' => 'ABC1D23',
            'identificacao' => 'VTR-01',
            'status' => 'Disponível',
        ]);
        $equipamento = Equipamento::create([
            'tipo_equipamento_id' => TipoEquipamento::first()->id,
            'nome' => 'Gerador portátil',
            'status' => 'Disponível',
        ]);

        $this->actingAs($gerente)->post(route('ocorrencias.atendimentos.store', $ocorrencia), [
            'equipe_id' => $equipe->id,
            'viatura_id' => $viatura->id,
            'equipamentos' => [$equipamento->id],
            'quantidades' => [$equipamento->id => 1],
        ])->assertRedirect(route('ocorrencias.show', $ocorrencia));

        $ocorrencia->refresh();
        $this->assertSame('Em Atendimento', $ocorrencia->status);
        $this->assertSame('Em Uso', $equipamento->refresh()->status);
        $this->assertSame('Em Serviço', $equipe->refresh()->status);
        $this->assertSame('Em Atendimento', $viatura->refresh()->status);

        $this->actingAs($gerente)->patch(route('ocorrencias.encerrar', $ocorrencia))
            ->assertRedirect(route('ocorrencias.show', $ocorrencia));

        $this->assertSame('Encerrada', $ocorrencia->refresh()->status);
        $this->assertSame('Disponível', $equipamento->refresh()->status);
        $this->assertSame('Disponível', $equipe->refresh()->status);
        $this->assertSame('Disponível', $viatura->refresh()->status);
    }

    public function test_nao_e_possivel_descartar_ocorrencia_com_atendimento_despachado(): void
    {
        $atendente = $this->criarUsuarioComPerfil('Atendente de Ocorrências');
        $ocorrencia = $this->criarOcorrenciaAberta();

        $this->actingAs($atendente)->patch(route('ocorrencias.triar', $ocorrencia), [
            'tipo_ocorrencia_id' => $ocorrencia->tipo_ocorrencia_id,
            'prioridade_id' => Prioridade::first()->id,
        ]);

        $equipe = Equipe::create([
            'tipo_equipe_id' => TipoEquipe::first()->id,
            'nome' => 'Equipe Alfa',
            'status' => 'Disponível',
        ]);

        $this->actingAs($atendente)->post(route('ocorrencias.atendimentos.store', $ocorrencia), [
            'equipe_id' => $equipe->id,
        ]);

        $response = $this->actingAs($atendente)->patch(route('ocorrencias.descartar', $ocorrencia));

        $response->assertSessionHas('error');
        $this->assertSame('Em Atendimento', $ocorrencia->refresh()->status);
    }
}
