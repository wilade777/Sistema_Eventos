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
        Schema::create('organizador_secretaria', function (Blueprint $table) {
            $table->foreignId('idOrganizador')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $table->foreignId('idSecretaria')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $table->primary(['idOrganizador', 'idSecretaria']); // Clave primaria compuesta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizador_secretaria');
    }
};