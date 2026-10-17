<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donativo_transferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donativo_origem_id')->constrained('donativos')->restrictOnDelete();
            $table->foreignId('donativo_destino_id')->constrained('donativos')->restrictOnDelete();
            $table->unsignedInteger('quantidade');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('data_hora');
            $table->text('observacoes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donativo_transferencias');
    }
};
