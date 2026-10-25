<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abrigos', function (Blueprint $table) {
            $table->string('telefone_responsavel', 20)->nullable()->after('responsavel');
        });
    }

    public function down(): void
    {
        Schema::table('abrigos', function (Blueprint $table) {
            $table->dropColumn('telefone_responsavel');
        });
    }
};
