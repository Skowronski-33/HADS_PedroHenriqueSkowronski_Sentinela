<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar os 4 perfis do Sentinela
        $roles = [
            'Admin',
            'Atendente de Ocorrências',
            'Gerente Operacional',
            'Gerenciador de Abrigo',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. Criar o usuário Admin inicial
        $admin = User::firstOrCreate(
            ['email' => 'admin@sentinela.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('sentinela2026'),
            ]
        );

        $admin->assignRole('Admin');

        $this->command->info('Perfis criados e usuário admin@sentinela.local pronto.');
    }
}