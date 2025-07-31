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
        Schema::table('tickets', function (Blueprint $table) {
            // Añadir codigoQR como string único y nullable después de 'precio'
            $table->string('codigoQR')->unique()->nullable()->after('precio');
            // Añadir columna 'usado' con valor por defecto false
            $table->boolean('usado')->default(false)->after('codigoQR');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('codigoQR');
            $table->dropColumn('usado');
        });
    }
};