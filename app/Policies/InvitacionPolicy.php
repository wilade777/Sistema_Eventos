<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Invitacion;
use Illuminate\Auth\Access\Response;

class InvitacionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        // Administradores, organizadores y secretarias pueden ver todas las invitaciones.
        // Los asistentes solo pueden ver sus propias invitaciones (se filtraría en el controlador).
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria() || $user->esAsistente();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Invitacion $invitacion): bool
    {
        // Administradores, organizadores y secretarias pueden ver cualquier invitación.
        // Un asistente solo puede ver su propia invitación.
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria() || ($user->esAsistente() && $user->idUsuario === $invitacion->idAsistente);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        // Organizadores y secretarias pueden crear invitaciones.
        return $user->esOrganizador() || $user->esSecretaria();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Invitacion $invitacion): bool
    {
        // Administradores, organizadores y secretarias pueden actualizar invitaciones.
        // Un asistente puede actualizar el estado de su propia invitación (RSVP).
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria() || ($user->esAsistente() && $user->idUsuario === $invitacion->idAsistente);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Invitacion $invitacion): bool
    {
        // Administradores, organizadores y secretarias pueden eliminar invitaciones.
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria();
    }

    // Método específico del diagrama
    public function actualizarRSVP(Usuario $user, Invitacion $invitacion): bool
    {
        // Un asistente puede actualizar el RSVP de su propia invitación.
        // Organizadores y secretarias pueden actualizar el RSVP de cualquier invitación.
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria() || ($user->esAsistente() && $user->idUsuario === $invitacion->idAsistente);
    }
}