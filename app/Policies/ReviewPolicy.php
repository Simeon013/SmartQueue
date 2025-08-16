<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Seuls les administrateurs et les super-administrateurs peuvent voir la liste des avis
        return $user->hasRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Review $review): bool
    {
        // Seuls les administrateurs et les super-administrateurs peuvent voir un avis
        return $user->hasRole(['admin', 'super-admin']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Les avis sont créés via un lien public, pas par les utilisateurs connectés
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): bool
    {
        // Les avis ne peuvent pas être modifiés après soumission
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        // Seuls les super-administrateurs peuvent supprimer un avis
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): bool
    {
        // Pas de fonctionnalité de restauration pour les avis
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        // Même règle que pour la suppression normale
        return $this->delete($user, $review);
    }
    
    /**
     * Determine whether the user can view review statistics.
     */
    public function viewStatistics(User $user): bool
    {
        // Seuls les administrateurs et les super-administrateurs peuvent voir les statistiques
        return $user->hasRole(['admin', 'super-admin']);
    }
}
