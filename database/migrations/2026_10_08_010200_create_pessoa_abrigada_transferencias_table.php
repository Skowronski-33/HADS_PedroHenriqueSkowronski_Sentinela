<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoa_abrigada_transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pessoa_abrigada_id')->constrained('pessoas_abrigadas')->cascadeOnDelete();
            $table->foreignId('abrigo_origem_id')->constrained('abrigos')->restrictOnDelete();
            $table->foreignId('abrigo_destino_id')->constrained('abrigos')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('data_hora');
            $table->text('observacoes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoa_abrigada_transferencias');
    }
};
