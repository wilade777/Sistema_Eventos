<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = 'idTicket';
    protected $table = 'tickets';

    protected $fillable = [
        'idEvento',
        'idAsistente',
        'codigoQR',
        'tipo',
        'precio',
    ];

    // Relaciones
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'idEvento', 'idEvento');
    }

    public function asistente()
    {
        return $this->belongsTo(Usuario::class, 'idAsistente', 'idUsuario');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'idTicket', 'idTicket');
    }
}