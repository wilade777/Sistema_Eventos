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
        Schema::create('asistencia_evento', function (Blueprint $table) {
            $table->foreignId('idAsistente')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $table->foreignId('idEvento')->constrained('eventos', 'idEvento')->onDelete('cascade');
            $table->boolean('confirmacionAsistencia')->default(false);
            $table->timestamps(); // Opcional, si quieres registrar cuándo se confirmó
            $table->primary(['idAsistente', 'idEvento']); // Clave primaria compuesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia_evento');
    }
};