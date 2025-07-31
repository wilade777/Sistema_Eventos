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
        Schema::create('invitacions', function (Blueprint $table) {
            $table->id('idInvitacion');
           $table->foreignId('idEvento')->constrained('eventos', 'idEvento')->onDelete('cascade');
$table->foreignId('idAsistente')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $table->string('estadoRSVP', 50); // 'pendiente', 'aceptada', 'rechazada'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitacions');
    }
};