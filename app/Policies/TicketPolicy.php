<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Ticket;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        // Administradores, Organizadores y Secretarias pueden ver todos los tickets.
        // Los asistentes solo pueden ver sus propios tickets (manejado en getMyTickets).
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Ticket $ticket): bool
    {
        // Administradores pueden ver cualquier ticket
        if ($user->esAdministrador()) {
            return true;
        }

        // El asistente dueño del ticket puede verlo
        if ($user->idUsuario === $ticket->idAsistente) {
            return true;
        }

        // El organizador del evento al que pertenece el ticket puede verlo
        if ($user->esOrganizador() && $user->idUsuario === $ticket->evento->idOrganizador) {
            return true;
        }

        // Una secretaria asignada al organizador del evento puede ver el ticket
        if ($user->esSecretaria()) {
            $organizador = $ticket->evento->organizador;
            // Asegurarse de que el organizador existe y la secretaria está asignada
            if ($organizador && $organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        // Cualquier usuario autenticado puede comprar un ticket (crear un ticket)
        // La lógica de negocio adicional (si es asistente, etc.) se manejará en el controlador.
        return $user !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Ticket $ticket): bool
    {
        // Administradores pueden actualizar cualquier ticket
        if ($user->esAdministrador()) {
            return true;
        }

        // El organizador del evento puede actualizar tickets de su evento (ej. marcar como usado)
        if ($user->esOrganizador() && $user->idUsuario === $ticket->evento->idOrganizador) {
            return true;
        }

        // Una secretaria asignada al organizador del evento puede actualizar el ticket (ej. marcar como usado)
        if ($user->esSecretaria()) {
            $organizador = $ticket->evento->organizador;
            if ($organizador && $organizador->secretariasAsignadas->contains($user->idUsuario)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Ticket $ticket): bool
    {
        // Solo administradores pueden eliminar tickets
        return $user->esAdministrador();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Usuario $user, Ticket $ticket): bool
    {
        return $user->esAdministrador();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Usuario $user, Ticket $ticket): bool
    {
        return $user->esAdministrador();
    }
}