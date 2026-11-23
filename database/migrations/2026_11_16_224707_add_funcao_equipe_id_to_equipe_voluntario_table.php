<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipe_voluntario', function (Blueprint $table) {
            $table->foreignId('funcao_equipe_id')->nullable()->after('voluntario_id')
                ->constrained('funcoes_equipe')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipe_voluntario', function (Blueprint $table) {
            $table->dropConstrainedForeignId('funcao_equipe_id');
        });
    }
};
