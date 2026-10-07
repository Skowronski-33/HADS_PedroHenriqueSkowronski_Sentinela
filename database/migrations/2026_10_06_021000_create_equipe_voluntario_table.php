<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipe_voluntario', function (Blueprint $table) {
            $table->foreignId('equipe_id')->constrained('equipes')->cascadeOnDelete();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->primary(['equipe_id', 'voluntario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipe_voluntario');
    }
};
