<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocorrencia_id')->constrained('ocorrencias')->restrictOnDelete();
            $table->foreignId('equipe_id')->nullable()->constrained('equipes')->restrictOnDelete();
            $table->foreignId('viatura_id')->nullable()->constrained('viaturas')->restrictOnDelete();
            $table->timestamp('data_hora_saida')->nullable();
            $table->timestamp('data_hora_retorno')->nullable();
            $table->string('status', 30)->default('Em Deslocamento');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimentos');
    }
};
