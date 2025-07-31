<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Importar para autenticación
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Para API Token

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'idUsuario'; // Especificar la clave primaria
    protected $table = 'usuarios'; // Especificar el nombre de la tabla

    protected $fillable = [
        'nombre',
        'correo',
        'contrasena', // En producción, nunca guardar contraseña sin hashear
        'rol',
    ];

    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    protected $casts = [
        // 'correo_verified_at' => 'datetime', // Si usas verificación de correo
        'contrasena' => 'hashed', // Laravel 11 sugiere esto si usas el campo 'password'
    ];

    // Relaciones (para roles específicos, si se usaran tablas separadas)
    public function eventosOrganizados()
    {
        return $this->hasMany(Evento::class, 'idOrganizador', 'idUsuario');
    }

    public function invitaciones()
    {
        return $this->hasMany(Invitacion::class, 'idAsistente', 'idUsuario');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'idAsistente', 'idUsuario');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaEvento::class, 'idAsistente', 'idUsuario');
    }

    // Métodos para verificar el rol
    public function esAdministrador(): bool
    {
        return $this->rol === 'Administrador';
    }

    public function esOrganizador(): bool
    {
        return $this->rol === 'Organizador';
    }

    public function esSecretaria(): bool
    {
        return $this->rol === 'Secretaria';
    }

    public function esAsistente(): bool
    {
        return $this->rol === 'Asistente';
    }
      // Relaciones para la gestión de secretarias
    public function secretariasAsignadas()
    {
        // Un organizador tiene muchas secretarias
        return $this->belongsToMany(Usuario::class, 'organizador_secretaria', 'idOrganizador', 'idSecretaria')
                    ->using(OrganizadorSecretaria::class) // Especificar el modelo pivote
                    ->withTimestamps();
    }

    public function organizadoresAsignados()
    {
        // Una secretaria pertenece a muchos organizadores
        return $this->belongsToMany(Usuario::class, 'organizador_secretaria', 'idSecretaria', 'idOrganizador')
                    ->using(OrganizadorSecretaria::class) // Especificar el modelo pivote
                    ->withTimestamps();
    }
}