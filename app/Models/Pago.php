<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $primaryKey = 'idPago';
    protected $table = 'pagos';

    protected $fillable = [
        'idTicket',
        'monto',
        'metodo',
        'estado',
    ];

    // Relaciones
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'idTicket', 'idTicket');
    }
}