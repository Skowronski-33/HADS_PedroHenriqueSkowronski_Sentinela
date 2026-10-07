<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viaturas', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('identificacao', 50);
            $table->string('modelo', 100)->nullable();
            $table->string('status', 30)->default('Disponível');
            $table->string('disponibilidade', 30)->default('Sim');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viaturas');
    }
};
