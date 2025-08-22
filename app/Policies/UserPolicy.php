<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Seuls les administrateurs peuvent voir la liste des utilisateurs
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Un utilisateur peut voir son propre profil
        if ($user->id === $model->id) {
            return true;
        }
        
        // Les administrateurs peuvent voir tous les utilisateurs
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            // Un admin ne peut voir que les agents, un super admin peut voir tout le monde
            return $user->isSuperAdmin() || $model->isAgent();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seuls les administrateurs peuvent créer des utilisateurs
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Un utilisateur ne peut pas se modifier lui-même via cette méthode
        if ($user->id === $model->id) {
            return false;
        }
        
        // Les super administrateurs peuvent modifier tout le monde sauf eux-mêmes
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Les administrateurs ne peuvent modifier que les agents
        if ($user->isAdmin()) {
            return $model->isAgent();
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Un utilisateur ne peut pas se supprimer lui-même
        if ($user->id === $model->id) {
            return false;
        }
        
        // Les super administrateurs peuvent supprimer tout le monde sauf d'autres super admins
        if ($user->isSuperAdmin()) {
            return !$model->isSuperAdmin();
        }
        
        // Les administrateurs ne peuvent supprimer que les agents
        if ($user->isAdmin()) {
            return $model->isAgent();
        }
        
        return false;
    }

    /**
     * Determine whether the user can manage user permissions.
     */
    public function managePermissions(User $user, User $model): bool
    {
        // Un utilisateur ne peut pas gérer ses propres permissions
        if ($user->id === $model->id) {
            return false;
        }

        // Les administrateurs peuvent gérer les permissions des agents
        if ($user->isAdmin()) {
            return $model->isAgent();
        }

        // Les super administrateurs peuvent gérer toutes les permissions
        if ($user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRole(User $user, User $model): bool
    {
        // Un utilisateur ne peut pas se modifier son propre rôle
        if ($user->id === $model->id) {
            return false;
        }

        // Seul le super administrateur peut attribuer des rôles
        return $user->isSuperAdmin();
    }
}
