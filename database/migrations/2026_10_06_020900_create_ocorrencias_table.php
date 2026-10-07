<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('tipo_ocorrencia_id')->constrained('tipos_ocorrencia')->restrictOnDelete();
            $table->foreignId('prioridade_id')->nullable()->constrained('prioridades')->nullOnDelete();
            $table->text('descricao');
            $table->string('endereco', 200);
            $table->string('bairro', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('ponto_referencia', 200)->nullable();
            $table->string('status', 30)->default('Aberta');
            $table->timestamp('data_hora');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocorrencias');
    }
};
