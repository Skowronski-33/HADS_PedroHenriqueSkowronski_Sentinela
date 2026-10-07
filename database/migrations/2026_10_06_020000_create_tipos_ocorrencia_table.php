<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_ocorrencia', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('descricao', 255)->nullable();
            $table->boolean('ativo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_ocorrencia');
    }
};
