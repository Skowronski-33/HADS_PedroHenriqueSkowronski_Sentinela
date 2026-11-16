<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_usuario_pode_logar_com_credenciais_validas(): void
    {
        $user = User::factory()->create(['password' => Hash::make('senha-correta')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-correta',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_usuario_nao_loga_com_senha_errada(): void
    {
        $user = User::factory()->create(['password' => Hash::make('senha-correta')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_inativo_nao_consegue_logar(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('senha-correta'),
            'ativo' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-correta',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_visitante_e_redirecionado_ao_acessar_rota_protegida(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}
