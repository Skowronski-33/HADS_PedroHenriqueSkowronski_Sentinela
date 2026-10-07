<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abrigo_id')->constrained('abrigos')->restrictOnDelete();
            $table->string('tipo', 100);
            $table->string('descricao', 200)->nullable();
            $table->string('unidade_medida', 30);
            $table->unsignedInteger('estoque_atual')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donativos');
    }
};
