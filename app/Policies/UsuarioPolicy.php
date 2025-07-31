<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class UsuarioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        // Solo administradores y el propio usuario pueden ver listas de usuarios (filtradas)
        // O si es un organizador/secretaria, pueden ver usuarios relevantes (asistentes)
        return $user->esAdministrador() || $user->esOrganizador() || $user->esSecretaria();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Usuario $model): bool
    {
        // Un administrador puede ver cualquier usuario
        // Un usuario puede ver su propio perfil
        // Un organizador/secretaria puede ver perfiles de asistentes o usuarios relevantes
        return $user->esAdministrador() || $user->idUsuario === $model->idUsuario || $user->esOrganizador() || $user->esSecretaria();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        // Solo administradores pueden crear usuarios (excepto el registro público)
        // La ruta de registro público ya maneja la creación de asistentes/usuarios iniciales
        // Esta política sería para creación por un admin desde un panel
        return $user->esAdministrador();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Usuario $model): bool
    {
        // Un administrador puede actualizar cualquier usuario
        // Un usuario puede actualizar su propio perfil
        return $user->esAdministrador() || $user->idUsuario === $model->idUsuario;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Usuario $model): bool
    {
        // Solo administradores pueden eliminar usuarios, y un usuario no puede eliminarse a sí mismo (por seguridad básica)
        return $user->esAdministrador() && $user->idUsuario !== $model->idUsuario;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Usuario $user, Usuario $model): bool
    {
        // Solo administradores pueden restaurar usuarios (si se implementa soft deletes)
        return $user->esAdministrador();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Usuario $user, Usuario $model): bool
    {
        // Solo administradores pueden eliminar permanentemente usuarios
        return $user->esAdministrador();
    }
}