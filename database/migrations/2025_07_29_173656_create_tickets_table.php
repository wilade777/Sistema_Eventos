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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('idTicket');
            $table->foreignId('idEvento')->constrained('eventos', 'idEvento')->onDelete('cascade');
$table->foreignId('idAsistente')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $table->string('codigoQR')->unique();
            $table->string('tipo', 100);
            $table->decimal('precio', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};