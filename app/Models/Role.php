<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    /**
     * Get the permissions for this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Assign a permission to this role.
     */
    public function assignPermission(Permission $permission)
    {
        return $this->permissions()->attach($permission->id);
    }

    /**
     * Remove a permission from this role.
     */
    public function removePermission(Permission $permission)
    {
        return $this->permissions()->detach($permission->id);
    }

    /**
     * Check if this role has a specific permission.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Find a role by its slug.
     */
    public static function findBySlug(string $slug)
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Check if a role exists by slug.
     */
    public static function existsBySlug(string $slug): bool
    {
        return static::where('slug', $slug)->exists();
    }
}
