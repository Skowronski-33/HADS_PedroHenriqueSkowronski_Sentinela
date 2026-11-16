<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Ocorrencia;
use App\Models\Prioridade;
use App\Models\TipoOcorrencia;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    public function test_triagem_de_ocorrencia_registra_atividade_com_usuario_e_subject(): void
    {
        $atendente = $this->criarUsuarioComPerfil('Atendente de Ocorrências');

        $ocorrencia = Ocorrencia::create([
            'user_id' => $atendente->id,
            'tipo_ocorrencia_id' => TipoOcorrencia::first()->id,
            'descricao' => 'Deslizamento de terra na encosta.',
            'endereco' => 'Rua da Encosta',
            'status' => 'Aberta',
            'data_hora' => now(),
        ]);

        $this->actingAs($atendente)->patch(route('ocorrencias.triar', $ocorrencia), [
            'tipo_ocorrencia_id' => $ocorrencia->tipo_ocorrencia_id,
            'prioridade_id' => Prioridade::first()->id,
        ]);

        $atividade = ActivityLog::where('acao', 'ocorrencia.triada')->first();

        $this->assertNotNull($atividade);
        $this->assertSame($atendente->id, $atividade->user_id);
        $this->assertTrue($atividade->subject->is($ocorrencia));
    }

    public function test_listagem_de_atividades_e_restrita_a_administradores(): void
    {
        $atendente = $this->criarUsuarioComPerfil('Atendente de Ocorrências');

        $this->actingAs($atendente)->get(route('atividades.index'))
            ->assertForbidden();
    }
}
