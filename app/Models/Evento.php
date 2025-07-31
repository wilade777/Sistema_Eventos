<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $primaryKey = 'idEvento';
    protected $table = 'eventos';

    protected $fillable = [
        'nombre',
        'fecha',
        'hora',
        'ubicacion',
        'descripcion',
        'estado',
        'imagenes',
        'idOrganizador',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i:s', // Para cast a objeto DateTime
        'imagenes' => 'array', // Laravel 11 puede manejar JSON a array/object
    ];

    // Relaciones
    public function organizador()
    {
        return $this->belongsTo(Usuario::class, 'idOrganizador', 'idUsuario');
    }

    public function invitaciones()
    {
        return $this->hasMany(Invitacion::class, 'idEvento', 'idEvento');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'idEvento', 'idEvento');
    }

    public function asistentes()
    {
        return $this->belongsToMany(Usuario::class, 'asistencia_evento', 'idEvento', 'idAsistente')
                    ->withPivot('confirmacionAsistencia')
                    ->withTimestamps();
    }
}