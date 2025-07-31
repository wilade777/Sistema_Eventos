<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizadorSecretaria extends Pivot
{
    use HasFactory;

    protected $table = 'organizador_secretaria';
    protected $primaryKey = ['idOrganizador', 'idSecretaria']; // Clave primaria compuesta
    public $incrementing = false; // No es auto-incrementing
    protected $fillable = [
        'idOrganizador',
        'idSecretaria',
    ];

    // No es necesario definir relaciones aquí si solo es una tabla pivote simple.
    // Las relaciones se definirán en el modelo Usuario.
}