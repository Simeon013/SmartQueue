<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Queue;
use App\Models\QueuePermission;
use App\Models\User;

trait HasPermissions
{
    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Check if the user has all of the given roles.
     */
    public function hasAllRoles(array $roleSlugs): bool
    {
        $userRoleSlugs = $this->roles()->pluck('slug')->toArray();
        return count(array_intersect($roleSlugs, $userRoleSlugs)) === count($roleSlugs);
    }

    /**
     * Check if the user has a specific permission through their roles.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionSlug) {
            $query->where('slug', $permissionSlug);
        })->exists();
    }

    /**
     * Check if the user has a specific permission (alias for hasPermission).
     */
    public function can($abilities, $arguments = []): bool
    {
        // Si c'est une permission simple
        if (is_string($abilities)) {
            $permission = $abilities;
            $model = $arguments[0] ?? null;
            
            // Si un modèle est fourni, vérifier les permissions granulaires
            if ($model) {
                if ($model instanceof Queue) {
                    return $this->hasQueuePermission($model, $permission);
                }
                // Pour d'autres modèles, on peut étendre ici
            }

            // Vérifier les permissions système
            return $this->hasPermission($permission);
        }

        // Pour les cas complexes, utiliser la méthode parent
        return parent::can($abilities, $arguments);
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionSlugs) {
            $query->whereIn('slug', $permissionSlugs);
        })->exists();
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissionSlugs): bool
    {
        $userPermissionSlugs = $this->getAllPermissions()->pluck('slug')->toArray();
        return count(array_intersect($permissionSlugs, $userPermissionSlugs)) === count($permissionSlugs);
    }

    /**
     * Get all permissions for this user through their roles.
     */
    public function getAllPermissions()
    {
        return Permission::whereHas('roles', function ($query) {
            $query->whereHas('users', function ($q) {
                $q->where('users.id', $this->id);
            });
        })->get();
    }

    /**
     * Check if the user has permission on a specific queue.
     */
    public function hasQueuePermission(Queue $queue, string $permissionType = null): bool
    {
        $query = $this->queuePermissions()->where('queue_id', $queue->id);

        if ($permissionType) {
            $query->where('permission_type', $permissionType);
        }

        return $query->exists();
    }

    /**
     * Check if the user can manage a specific queue (owner or manager).
     */
    public function canManageQueue(Queue $queue): bool
    {
        return $queue->userCanManage($this);
    }

    /**
     * Check if the user owns a specific queue.
     */
    public function ownsQueue(Queue $queue): bool
    {
        return $queue->userOwns($this);
    }

    /**
     * Check if the user is a manager of a specific queue.
     */
    public function isManagerOfQueue(Queue $queue): bool
    {
        return $queue->userHasPermission($this, 'manager');
    }

    /**
     * Check if the user is an operator of a specific queue.
     */
    public function isOperatorOfQueue(Queue $queue): bool
    {
        return $queue->userCanOperate($this);
    }

    /**
     * Get all queues the user has access to.
     */
    public function getAccessibleQueues()
    {
        return Queue::whereHas('permissions', function ($query) {
            $query->where('user_id', $this->id);
        });
    }

    /**
     * Get all queues the user owns.
     */
    public function getOwnedQueues()
    {
        return Queue::whereHas('permissions', function ($query) {
            $query->where('user_id', $this->id)
                  ->where('permission_type', 'owner');
        });
    }

    /**
     * Get all queues the user can manage.
     */
    public function getManageableQueues()
    {
        return Queue::whereHas('permissions', function ($query) {
            $query->where('user_id', $this->id)
                  ->whereIn('permission_type', ['owner', 'manager']);
        });
    }

    /**
     * Assign a role to this user.
     */
    public function assignRole(Role $role)
    {
        if (!$this->hasRole($role->slug)) {
            return $this->roles()->attach($role->id);
        }
        return false;
    }

    /**
     * Remove a role from this user.
     */
    public function removeRole(Role $role)
    {
        return $this->roles()->detach($role->id);
    }

    /**
     * Sync roles for this user (replace all existing roles).
     */
    public function syncRoles(array $roleIds)
    {
        return $this->roles()->sync($roleIds);
    }

    /**
     * Grant permission on a queue to this user.
     */
    public function grantQueuePermission(Queue $queue, string $permissionType, User $grantedBy = null)
    {
        return $this->queuePermissions()->updateOrCreate(
            ['queue_id' => $queue->id],
            [
                'permission_type' => $permissionType,
                'granted_by' => $grantedBy ? $grantedBy->id : null
            ]
        );
    }

    /**
     * Revoke permission on a queue from this user.
     */
    public function revokeQueuePermission(Queue $queue)
    {
        return $this->queuePermissions()->where('queue_id', $queue->id)->delete();
    }

    /**
     * Check if user is a super admin (has super-admin role).
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is an admin (has admin role).
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }

    /**
     * Check if user is an agent manager (has agent-manager role).
     */
    public function isAgentManager(): bool
    {
        return $this->hasRole('agent-manager') || $this->isAdmin();
    }

    /**
     * Check if user is an agent (has agent role).
     */
    public function isAgent(): bool
    {
        return $this->hasRole('agent') || $this->isAgentManager();
    }
}
