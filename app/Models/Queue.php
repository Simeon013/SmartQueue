<?php

namespace App\Models;

use App\Enums\QueueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'establishment_id',
        'status',
        'created_by'
    ];
    
    protected $with = ['creator'];

    protected $casts = [
        'status' => QueueStatus::class,
    ];
    
    protected $attributes = [
        'status' => QueueStatus::OPEN->value,
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
     * Check if a user can operate this queue (manage tickets).
     */
    public function userCanOperate(User $user): bool
    {
        // Vérifier d'abord les permissions globales
        if ($this->permissions()
            ->whereNull('user_id')
            ->whereIn('permission_type', ['owner', 'manager', 'operator'])
            ->exists()) {
            return true;
        }

        // Vérifier les permissions individuelles
        return $this->permissions()
            ->where('user_id', $user->id)
            ->whereIn('permission_type', ['owner', 'manager', 'operator'])
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
    
    /**
     * Scope a query to only include open queues.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', QueueStatus::OPEN->value);
    }
    
    /**
     * Scope a query to only include paused queues.
     */
    public function scopePaused($query)
    {
        return $query->where('status', QueueStatus::PAUSED->value);
    }
    
    /**
     * Scope a query to only include blocked queues.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', QueueStatus::BLOCKED->value);
    }
    
    /**
     * Scope a query to only include closed queues.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', QueueStatus::CLOSED->value);
    }
    
    /**
     * Scope a query to only include active queues (open or blocked).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            QueueStatus::OPEN->value, 
            QueueStatus::BLOCKED->value
        ]);
    }
    
    /**
     * Check if the queue is open.
     */
    public function isOpen(): bool
    {
        return $this->status === QueueStatus::OPEN;
    }
    
    /**
     * Check if the queue is paused.
     */
    public function isPaused(): bool
    {
        return $this->status === QueueStatus::PAUSED;
    }
    
    /**
     * Check if the queue is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->status === QueueStatus::BLOCKED;
    }
    
    /**
     * Check if the queue is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === QueueStatus::CLOSED;
    }
    
    /**
     * Check if the queue can create new tickets.
     */
    public function canCreateTickets(): bool
    {
        return $this->status->canCreateTickets();
    }
    
    /**
     * Check if the queue can process tickets.
     */
    public function canProcessTickets(): bool
    {
        return $this->status->canProcessTickets();
    }
    
    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }
    
    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }
}
