<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('viaturas', function (Blueprint $table) {
            $table->string('tipo', 30)->nullable()->after('identificacao');
        });
    }

    public function down(): void
    {
        Schema::table('viaturas', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
