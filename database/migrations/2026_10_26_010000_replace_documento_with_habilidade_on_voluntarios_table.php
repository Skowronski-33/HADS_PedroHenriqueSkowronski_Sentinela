<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn('documento');
            $table->string('habilidade', 100)->nullable()->after('telefone');
        });
    }

    public function down(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn('habilidade');
            $table->string('documento', 30)->nullable()->after('telefone');
        });
    }
};
