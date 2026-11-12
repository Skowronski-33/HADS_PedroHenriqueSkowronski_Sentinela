<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndAdminSeeder::class,
            LookupDataSeeder::class,
            VoluntarioSeeder::class,
            EquipamentoSeeder::class,
            ViaturaSeeder::class,
            EquipeSeeder::class,
            AbrigoSeeder::class,
            DonativoSeeder::class,
            PessoaAbrigadaSeeder::class,
            OcorrenciaSeeder::class,
        ]);
    }
}