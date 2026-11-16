<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\LookupDataSeeder;
use Database\Seeders\RoleAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleAndAdminSeeder::class, LookupDataSeeder::class]);
    }

    /**
     * Cria um usuário já com o perfil (role) informado, para testes de autorização
     * por rota (ex.: role:Gerenciador de Abrigo|Admin em routes/web.php).
     */
    protected function criarUsuarioComPerfil(string $perfil): User
    {
        $user = User::factory()->create();
        $user->assignRole($perfil);

        return $user;
    }
}
