<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\QueuePermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    /**
     * Get the user's role.
     */
    public function getRole(): UserRole
    {
        return $this->role ?? UserRole::AGENT;
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->getRole(), $roles);
    }

    /**
     * Check if the user has a specific permission.
     */
    public function can($ability, $arguments = []): bool
    {
        // Si c'est une permission de modèle, on la traite séparément
        if (!is_string($ability) || str_contains($ability, '\\')) {
            return parent::can($ability, $arguments);
        }

        return $this->getRole()->can($ability);
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(UserRole::SUPER_ADMIN);
    }

    /**
     * Check if the user is an admin.
     * Includes super admins as they have all admin privileges.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ADMIN) || $this->isSuperAdmin();
    }

    /**
     * Check if the user is an agent.
     */
    public function isAgent(): bool
    {
        return $this->hasRole(UserRole::AGENT);
    }

    // Méthodes de compatibilité pour les vues existantes
    // Ces méthodes retournent des collections vides car nous n'utilisons plus les rôles dynamiques
    
    /**
     * Compatibilité avec l'ancien système de rôles
     */
    public function roles()
    {
        return collect([
            (object)['slug' => $this->role->value]
        ]);
    }
    
    /**
     * Vérifie si l'utilisateur a un rôle spécifique (compatibilité)
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->getRole()->value === $role;
        }
        
        if ($role instanceof UserRole) {
            return $this->getRole() === $role;
        }
        
        return false;
    }
    
    /**
     * Gestion des files d'attente accessibles par l'utilisateur
     * À implémenter selon la logique de votre application
     */
    public function accessibleQueues()
    {
        // Les administrateurs voient toutes les files
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return Queue::query();
        }
        
        // Les agents ne voient que les files qui leur sont assignées
        // À adapter selon votre logique métier
        return Queue::where('assigned_to', $this->id);
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
    
    /**
     * Get the queue permissions for the user.
     */
    public function queuePermissions()
    {
        return $this->hasMany(QueuePermission::class);
    }
}
