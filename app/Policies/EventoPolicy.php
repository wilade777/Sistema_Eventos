<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Evento;
use Illuminate\Auth\Access\Response;

class EventoPolicy
{
    // Un administrador puede realizar cualquier acción
    public function before(Usuario $user, string $ability)
    {
        if ($user->esAdministrador()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        // Todos los usuarios autenticados pueden ver la lista de eventos.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Evento $evento): bool
    {
        // Todos los usuarios autenticados pueden ver un evento específico.
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        // Solo los organizadores pueden crear eventos.
        return $user->esOrganizador();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Evento $evento): bool
    {
        // Solo el organizador del evento puede actualizarlo.
        return $user->esOrganizador() && $user->idUsuario === $evento->idOrganizador;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Evento $evento): bool
    {
        // Solo el organizador del evento puede eliminarlo.
        return $user->esOrganizador() && $user->idUsuario === $evento->idOrganizador;
    }

    // Métodos específicos del diagrama
    public function publicar(Usuario $user, Evento $evento): bool
    {
        return $user->esOrganizador() && $user->idUsuario === $evento->idOrganizador;
    }

    public function ocultar(Usuario $user, Evento $evento): bool
    {
        return $user->esOrganizador() && $user->idUsuario === $evento->idOrganizador;
    }

    public function cancelar(Usuario $user, Evento $evento): bool
    {
        return $user->esOrganizador() && $user->idUsuario === $evento->idOrganizador;
    }

    public function confirmarAsistencia(Usuario $user, Evento $evento): bool
    {
        // Un asistente puede confirmar su propia asistencia.
        // Un organizador o secretaria pueden confirmar asistencia de otros.
        return $user->esAsistente() || $user->esOrganizador() || $user->esSecretaria();
    }

    public function obtenerAsistentes(Usuario $user, Evento $evento): bool
    {
        // El organizador del evento, o una secretaria pueden ver la lista de asistentes.
        return ($user->esOrganizador() && $user->idUsuario === $evento->idOrganizador) || $user->esSecretaria();
    }
}