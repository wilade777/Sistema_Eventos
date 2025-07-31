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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('idUsuario'); // Renombrado de 'id' a 'idUsuario' para coincidir
            $table->string('nombre');
            $table->string('correo')->unique();
            $table->string('contrasena'); // En producción, usar `password` y hashear
            $table->string('rol', 50); // 'Administrador', 'Organizador', 'Secretaria', 'Asistente'
            $table->rememberToken(); // Necesario para autenticación por defecto de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};