<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Pago;
use Illuminate\Auth\Access\Response;

class PagoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        // Administradores, organizadores y secretarias pueden ver todos los pagos.
        // Los asistentes solo pueden ver los pagos de sus propios tickets (se filtraría en el controlador).
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria() || $user->esAsistente();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Pago $pago): bool
    {
        // Administradores, organizadores y secretarias pueden ver cualquier pago.
        // Un asistente solo puede ver los pagos de sus tickets.
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria() || ($user->esAsistente() && $user->idUsuario === $pago->ticket->idAsistente);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        // Asistentes pueden crear pagos (al comprar un ticket).
        // Organizadores y secretarias también pueden registrar pagos.
        return $user->esAsistente() || $user->esOrganizador() || $user->esSecretaria();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Pago $pago): bool
    {
        // Administradores, organizadores y secretarias pueden actualizar pagos.
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Pago $pago): bool
    {
        // Administradores, organizadores y secretarias pueden eliminar pagos.
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria();
    }

    // Métodos específicos del diagrama
    public function procesarPago(Usuario $user, Pago $pago): bool
    {
        // Organizadores y secretarias pueden procesar pagos.
        return $user->esOrganizador() || $user->esSecretaria();
    }

    public function verificarPago(Usuario $user, Pago $pago): bool
    {
        // Cualquier usuario autenticado puede verificar un pago (si tiene el ID del pago).
        // En un escenario real, esto podría ser más restrictivo.
        return true;
    }
}