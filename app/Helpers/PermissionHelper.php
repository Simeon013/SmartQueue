<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Queue;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if the current user has a specific permission.
     */
    public static function can(string $permission): bool
    {
        $user = Auth::user();
        return $user ? $user->hasPermission($permission) : false;
    }

    /**
     * Check if the current user has any of the given permissions.
     */
    public static function canAny(array $permissions): bool
    {
        $user = auth()->user();
        return $user ? $user->hasAnyPermission($permissions) : false;
    }

    /**
     * Check if the current user has all of the given permissions.
     */
    public static function canAll(array $permissions): bool
    {
        $user = auth()->user();
        return $user ? $user->hasAllPermissions($permissions) : false;
    }

    /**
     * Check if the current user has a specific role.
     */
    public static function hasRole(string $role): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole($role) : false;
    }

    /**
     * Check if the current user has any of the given roles.
     */
    public static function hasAnyRole(array $roles): bool
    {
        $user = auth()->user();
        return $user ? $user->hasAnyRole($roles) : false;
    }

    /**
     * Check if the current user can manage a specific queue.
     */
    public static function canManageQueue(Queue $queue): bool
    {
        $user = auth()->user();
        return $user ? $user->canManageQueue($queue) : false;
    }

    /**
     * Check if the current user owns a specific queue.
     */
    public static function ownsQueue(Queue $queue): bool
    {
        $user = auth()->user();
        return $user ? $user->ownsQueue($queue) : false;
    }

    /**
     * Check if the current user has permission on a specific queue.
     */
    public static function hasQueuePermission(Queue $queue, string $permissionType = null): bool
    {
        $user = auth()->user();
        return $user ? $user->hasQueuePermission($queue, $permissionType) : false;
    }

    /**
     * Get all permissions for the current user.
     */
    public static function getUserPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();
        return $user ? $user->getAllPermissions() : collect();
    }

    /**
     * Get all roles for the current user.
     */
    public static function getUserRoles(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();
        return $user ? $user->roles : collect();
    }

    /**
     * Get all accessible queues for the current user.
     */
    public static function getAccessibleQueues()
    {
        $user = auth()->user();
        return $user ? $user->getAccessibleQueues() : collect();
    }

    /**
     * Get all manageable queues for the current user.
     */
    public static function getManageableQueues()
    {
        $user = auth()->user();
        return $user ? $user->getManageableQueues() : collect();
    }

    /**
     * Get all owned queues for the current user.
     */
    public static function getOwnedQueues()
    {
        $user = auth()->user();
        return $user ? $user->getOwnedQueues() : collect();
    }

    /**
     * Check if the current user is a super admin.
     */
    public static function isSuperAdmin(): bool
    {
        $user = auth()->user();
        return $user ? $user->isSuperAdmin() : false;
    }

    /**
     * Check if the current user is an admin.
     */
    public static function isAdmin(): bool
    {
        $user = auth()->user();
        return $user ? $user->isAdmin() : false;
    }

    /**
     * Check if the current user is an agent manager.
     */
    public static function isAgentManager(): bool
    {
        $user = auth()->user();
        return $user ? $user->isAgentManager() : false;
    }

    /**
     * Check if the current user is an agent.
     */
    public static function isAgent(): bool
    {
        $user = auth()->user();
        return $user ? $user->isAgent() : false;
    }

    /**
     * Get all available permissions.
     */
    public static function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::all();
    }

    /**
     * Get all available roles.
     */
    public static function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::all();
    }

    /**
     * Get permissions by group.
     */
    public static function getPermissionsByGroup(): array
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $group = explode('.', $permission->slug)[0];
            $grouped[$group][] = $permission;
        }

        return $grouped;
    }
}
