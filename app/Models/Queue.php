<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'establishment_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($queue) {
            if (empty($queue->code)) {
                do {
                    $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                } while (self::where('code', $code)->exists());
                $queue->code = $code;
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function events()
    {
        return $this->hasMany(QueueEvent::class);
    }

    public function activeTickets()
    {
        return $this->tickets()->whereIn('status', ['waiting', 'called']);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    /**
     * Get the permissions for this queue.
     */
    public function permissions()
    {
        return $this->hasMany(QueuePermission::class);
    }

    /**
     * Get the users who have permissions on this queue.
     */
    public function authorizedUsers()
    {
        return $this->belongsToMany(User::class, 'queue_permissions')
            ->withPivot('permission_type', 'granted_by')
            ->withTimestamps();
    }

    /**
     * Get the owners of this queue.
     */
    public function owners()
    {
        return $this->belongsToMany(User::class, 'queue_permissions')
            ->wherePivot('permission_type', 'owner');
    }

    /**
     * Get the managers of this queue.
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'queue_permissions')
            ->wherePivot('permission_type', 'manager');
    }

    /**
     * Get the operators of this queue.
     */
    public function operators()
    {
        return $this->belongsToMany(User::class, 'queue_permissions')
            ->wherePivot('permission_type', 'operator');
    }

    /**
     * Check if a user has permission on this queue.
     */
    public function userHasPermission(User $user, string $permissionType = null): bool
    {
        // Vérifier d'abord les permissions globales (user_id = null)
        $globalQuery = $this->permissions()->whereNull('user_id');
        if ($permissionType) {
            $globalQuery->where('permission_type', $permissionType);
        }
        
        if ($globalQuery->exists()) {
            return true;
        }

        // Vérifier les permissions individuelles
        $query = $this->permissions()->where('user_id', $user->id);
        if ($permissionType) {
            $query->where('permission_type', $permissionType);
        }

        return $query->exists();
    }

    /**
     * Check if a user can manage this queue.
     */
    public function userCanManage(User $user): bool
    {
        // Vérifier d'abord les permissions globales
        if ($this->permissions()
            ->whereNull('user_id')
            ->whereIn('permission_type', ['owner', 'manager'])
            ->exists()) {
            return true;
        }

        // Vérifier les permissions individuelles
        return $this->permissions()
            ->where('user_id', $user->id)
            ->whereIn('permission_type', ['owner', 'manager'])
            ->exists();
    }

    /**
     * Check if a user owns this queue.
     */
    public function userOwns(User $user): bool
    {
        // Vérifier d'abord les permissions globales
        if ($this->permissions()
            ->whereNull('user_id')
            ->where('permission_type', 'owner')
            ->exists()) {
            return true;
        }

        // Vérifier les permissions individuelles
        return $this->permissions()
            ->where('user_id', $user->id)
            ->where('permission_type', 'owner')
            ->exists();
    }

    /**
     * Grant permission to a user on this queue.
     */
    public function grantPermissionTo(User $user, string $permissionType, User $grantedBy = null)
    {
        return $this->permissions()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'permission_type' => $permissionType,
                'granted_by' => $grantedBy ? $grantedBy->id : null
            ]
        );
    }

    /**
     * Revoke permission from a user on this queue.
     */
    public function revokePermissionFrom(User $user)
    {
        return $this->permissions()->where('user_id', $user->id)->delete();
    }
}
