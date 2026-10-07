<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes_donativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donativo_id')->constrained('donativos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo_movimentacao', 20);
            $table->unsignedInteger('quantidade');
            $table->timestamp('data_hora');
            $table->text('observacoes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_donativos');
    }
};
