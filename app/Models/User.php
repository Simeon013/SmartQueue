<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use App\Traits\HasPermissions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the roles for this user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Get the queue permissions for this user.
     */
    public function queuePermissions()
    {
        return $this->hasMany(QueuePermission::class);
    }

    /**
     * Get the queues this user has permissions for.
     */
    public function accessibleQueues()
    {
        return Queue::whereHas('permissions', function ($query) {
            $query->where('user_id', $this->id);
        });
    }

    /**
     * Get the queues this user owns.
     */
    public function ownedQueues()
    {
        return Queue::whereHas('permissions', function ($query) {
            $query->where('user_id', $this->id)
                  ->where('permission_type', 'owner');
        });
    }

    /**
     * Get the queues this user can manage.
     */
    public function manageableQueues()
    {
        return Queue::whereHas('permissions', function ($query) {
            $query->where('user_id', $this->id)
                  ->whereIn('permission_type', ['owner', 'manager']);
        });
    }

    /**
     * Get the IDs of queues this user has any permission for.
     *
     * @return array
     */
    public function getAccessibleQueueIds(): array
    {
        // Récupérer les permissions individuelles
        $individualPermissions = $this->queuePermissions()->pluck('queue_id')->unique();
        
        // Récupérer les permissions globales (user_id = null)
        $globalPermissions = \App\Models\QueuePermission::whereNull('user_id')->pluck('queue_id')->unique();
        
        // Combiner et retourner
        return $individualPermissions->merge($globalPermissions)->unique()->toArray();
    }

        /**
     * Check if the user is an admin (legacy method for backward compatibility).
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin') || $this->hasRole('super-admin');
    }

    /**
     * Check if the user is an agent (legacy method for backward compatibility).
     *
     * @return bool
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent' || $this->hasRole('agent') || $this->hasRole('agent-manager');
    }

    public function managedQueues()
    {
        return Queue::whereHas('establishment', function ($query) {
            $query->whereHas('admins', function ($q) {
                $q->where('users.id', $this->id);
            });
        });
    }

    /**
     * Log an audit event for this user.
     */
    public function logAuditEvent(string $action, array $data = []): void
    {
        Log::info('User Audit', [
            'user_id' => $this->id,
            'user_name' => $this->name,
            'user_email' => $this->email,
            'action' => $action,
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get audit logs for this user.
     */
    public function getAuditLogs(int $limit = 50): array
    {
        // This would typically query a dedicated audit table
        // For now, we'll return a placeholder
        return [
            'message' => 'Audit logs would be stored in a dedicated table',
            'user_id' => $this->id,
            'limit' => $limit,
        ];
    }
}
