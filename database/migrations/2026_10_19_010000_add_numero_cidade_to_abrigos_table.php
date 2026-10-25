<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abrigos', function (Blueprint $table) {
            $table->string('numero', 10)->nullable()->after('endereco');
            $table->string('cidade', 100)->nullable()->after('bairro');
        });
    }

    public function down(): void
    {
        Schema::table('abrigos', function (Blueprint $table) {
            $table->dropColumn(['numero', 'cidade']);
        });
    }
};
