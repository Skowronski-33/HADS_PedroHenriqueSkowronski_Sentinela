<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocupacoes_abrigos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abrigo_id')->constrained('abrigos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo_movimentacao', 20);
            $table->unsignedInteger('quantidade_pessoas');
            $table->timestamp('data_hora');
            $table->text('observacoes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocupacoes_abrigos');
    }
};
