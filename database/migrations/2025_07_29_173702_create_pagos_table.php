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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('idPago');
           $table->foreignId('idTicket')->constrained('tickets', 'idTicket')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->string('metodo', 100);
            $table->string('estado', 50); // 'pendiente', 'completado', 'fallido'
            $table->timestamps(); // Laravel ya añade created_at y updated_at, puedes renombrar updated_at a fechaPago si lo prefieres o añadir uno nuevo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};