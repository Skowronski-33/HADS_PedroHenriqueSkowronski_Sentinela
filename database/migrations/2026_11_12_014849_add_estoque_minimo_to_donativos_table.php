<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donativos', function (Blueprint $table) {
            $table->unsignedInteger('estoque_minimo')->default(10)->after('estoque_atual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donativos', function (Blueprint $table) {
            $table->dropColumn('estoque_minimo');
        });
    }
};
