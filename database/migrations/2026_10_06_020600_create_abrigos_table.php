<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abrigos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('endereco', 200);
            $table->string('bairro', 100)->nullable();
            $table->unsignedInteger('capacidade');
            $table->string('responsavel', 100)->nullable();
            $table->string('status', 30)->default('Ativo');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abrigos');
    }
};
