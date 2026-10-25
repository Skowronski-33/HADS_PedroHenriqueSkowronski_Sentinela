<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipamentos', function (Blueprint $table) {
            $table->string('patrimonio', 50)->nullable()->after('nome');
            $table->string('localizacao', 100)->nullable()->after('patrimonio');
            $table->date('data_ultima_manutencao')->nullable()->after('localizacao');
        });
    }

    public function down(): void
    {
        Schema::table('equipamentos', function (Blueprint $table) {
            $table->dropColumn(['patrimonio', 'localizacao', 'data_ultima_manutencao']);
        });
    }
};
