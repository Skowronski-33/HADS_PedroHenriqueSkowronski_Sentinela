<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pessoas_abrigadas', function (Blueprint $table) {
            $table->foreignId('responsavel_familiar_id')->nullable()
                ->after('abrigo_id')
                ->constrained('pessoas_abrigadas')->nullOnDelete();
            $table->boolean('necessita_prioridade')->default(false)->after('contato');
        });
    }

    public function down(): void
    {
        Schema::table('pessoas_abrigadas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsavel_familiar_id');
            $table->dropColumn('necessita_prioridade');
        });
    }
};
