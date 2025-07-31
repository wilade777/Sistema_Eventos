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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id('idEvento');
            $table->string('nombre');
            $table->date('fecha');
            $table->time('hora');
            $table->string('ubicacion');
            $table->text('descripcion')->nullable();
            $table->string('estado', 50); // Ej: 'activo', 'cancelado', 'finalizado'
            $table->json('imagenes')->nullable(); // Almacena un array JSON de URLs
            $table->foreignId('idOrganizador')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            // Especifica que idOrganizador en eventos se refiere a idUsuario en usuarios
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};