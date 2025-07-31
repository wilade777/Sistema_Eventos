<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Para aplicaciones API, no necesitamos redirigir a una ruta web.
        // Si la solicitud no está autenticada, Laravel devolverá automáticamente
        // una respuesta 401 Unauthorized, que es lo esperado para APIs.
        return null; // Asegúrate de que esta línea sea la única en el cuerpo del método.
    }
}