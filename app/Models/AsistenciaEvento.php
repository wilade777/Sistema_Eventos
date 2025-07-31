<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AsistenciaEvento extends Pivot
{
    use HasFactory;

    protected $table = 'asistencia_evento';

    protected $fillable = [
        'idAsistente',
        'idEvento',
        'confirmacionAsistencia',
    ];

    protected $casts = [
        'confirmacionAsistencia' => 'boolean',
    ];
}