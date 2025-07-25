<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
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
        
        // Vérifier les permissions spécifiques aux modèles
        if (str_contains($ability, '.') && count($arguments) > 0) {
            $model = $arguments[0] ?? null;
            
            // Gestion des permissions spécifiques aux utilisateurs
            if ($model instanceof self) {
                return $this->canManageUser($ability, $model);
            }
        }

        return $this->getRole()->can($ability);
    }
    
    /**
     * Vérifie si l'utilisateur peut gérer un autre utilisateur
     */
    protected function canManageUser(string $ability, User $targetUser): bool
    {
        // Un utilisateur ne peut pas se gérer lui-même via ces méthodes
        if ($this->id === $targetUser->id) {
            return false;
        }
        
        // Le super admin peut tout faire
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Un admin ne peut gérer que les agents
        if ($this->isAdmin()) {
            return $targetUser->isAgent();
        }
        
        // Les agents ne peuvent gérer personne
        return false;
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
        if (!$this->role) {
            return collect();
        }
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
     * Get all queues where the user has any permission.
     * Inclut les files avec permissions spécifiques et globales.
     */
    public function accessibleQueues()
    {
        // Super admins et admins voient toutes les files
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return Queue::query();
        }
        
        // Récupérer les IDs des files accessibles
        $queueIds = $this->getAccessibleQueueIds();
        
        // Si l'utilisateur n'a aucune permission, retourner une requête vide
        if (empty($queueIds)) {
            return Queue::where('id', 0); // Retourne une requête vide
        }
        
        // Retourner les files correspondant aux IDs accessibles
        return Queue::whereIn('id', $queueIds);
    }
    
    /**
     * Check if user has any permission on the given queue.
     */
    public function hasAnyQueuePermission(Queue $queue): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }
        
        return $this->queuePermissions()
            ->where('queue_id', $queue->id)
            ->exists();
    }
    
    /**
     * Check if user has specific permission on the given queue.
     * Vérifie à la fois les permissions spécifiques et les permissions globales.
     */
    public function hasQueuePermission(Queue $queue, string $permissionType): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }
        
        // Vérifier les permissions globales (user_id = null)
        $globalPermission = DB::table('queue_permissions')
            ->where('queue_id', $queue->id)
            ->whereNull('user_id')
            ->where('permission_type', $permissionType)
            ->exists();
            
        if ($globalPermission) {
            return true;
        }
        
        // Vérifier les permissions spécifiques à l'utilisateur
        return $this->queuePermissions()
            ->where('queue_id', $queue->id)
            ->where('permission_type', $permissionType)
            ->exists();
    }

    /**
     * Retourne les IDs des files d'attente accessibles à l'utilisateur selon son rôle et ses permissions.
     */
    public function getAccessibleQueueIds()
    {
        // Admins et super-admins : toutes les files
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return \App\Models\Queue::pluck('id')->toArray();
        }
        
        // Récupérer les IDs des files avec permissions spécifiques
        $specificQueueIds = $this->queuePermissions()->pluck('queue_id')->toArray();
        
        // Récupérer les IDs des files avec permissions globales (user_id = null)
        $globalQueueIds = \App\Models\QueuePermission::whereNull('user_id')
            ->pluck('queue_id')
            ->toArray();
        
        // Fusionner et supprimer les doublons
        return array_unique(array_merge($specificQueueIds, $globalQueueIds));
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
     * Retourne uniquement les permissions spécifiques à l'utilisateur.
     * Les permissions globales sont gérées séparément.
     */
    public function queuePermissions()
    {
        return $this->hasMany(QueuePermission::class);
    }}
