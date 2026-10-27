<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropColumn('observacoes');
        });
    }

    public function down(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->text('observacoes')->nullable()->after('ativo');
        });
    }
};
