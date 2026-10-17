<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoas_abrigadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abrigo_id')->constrained('abrigos')->restrictOnDelete();
            $table->string('nome', 150);
            $table->unsignedSmallInteger('idade')->nullable();
            $table->string('contato', 30)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamp('data_entrada');
            $table->timestamp('data_saida')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pessoas_abrigadas');
    }
};
