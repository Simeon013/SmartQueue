<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueuePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'queue_id',
        'permission_type',
        'granted_by'
    ];

    protected $casts = [
        'permission_type' => 'string'
    ];

    /**
     * Get the user that has this permission.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the queue this permission applies to.
     */
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * Get the user who granted this permission.
     */
    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if this permission is of a specific type.
     */
    public function isType(string $type): bool
    {
        return $this->permission_type === $type;
    }

    /**
     * Check if this permission allows management (owner or manager).
     */
    public function allowsManagement(): bool
    {
        return in_array($this->permission_type, ['owner', 'manager']);
    }

    /**
     * Check if this permission is owner level.
     */
    public function isOwner(): bool
    {
        return $this->permission_type === 'owner';
    }

    /**
     * Check if this permission is manager level.
     */
    public function isManager(): bool
    {
        return $this->permission_type === 'manager';
    }

    /**
     * Check if this permission is operator level.
     */
    public function isOperator(): bool
    {
        return $this->permission_type === 'operator';
    }

    /**
     * Scope to filter by permission type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('permission_type', $type);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope to filter by queue.
     */
    public function scopeForQueue($query, Queue $queue)
    {
        return $query->where('queue_id', $queue->id);
    }
}
