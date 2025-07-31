<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitacion extends Model
{
    use HasFactory;

    protected $primaryKey = 'idInvitacion';
    protected $table = 'invitacions';

    protected $fillable = [
        'idEvento',
        'idAsistente',
        'estadoRSVP',
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
}
