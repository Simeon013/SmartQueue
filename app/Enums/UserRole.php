<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case AGENT = 'agent';

    /**
     * Get all available roles
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN->value => 'Super Administrateur',
            self::ADMIN->value => 'Administrateur',
            self::AGENT->value => 'Agent',
        ];
    }

    /**
     * Get role label
     */
    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::AGENT => 'Agent',
        };
    }

    /**
     * Check if the role can perform an action
     */
    public function can(string $permission): bool
    {
        $permissions = $this->getPermissions();
        return in_array($permission, $permissions);
    }

    /**
     * Get role description
     */
    public function getDescription(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Accès complet à toutes les fonctionnalités du système',
            self::ADMIN => 'Peut gérer les utilisateurs et les files d\'attente',
            self::AGENT => 'Peut gérer les tickets dans les files d\'attente assignées',
        };
    }

    /**
     * Get all permissions for this role
     */
    public function getPermissions(): array
    {
        return match($this) {
            self::SUPER_ADMIN => [
                // Gestion des utilisateurs
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
                'users.manage_roles',
                
                // Gestion des files d'attente
                'queues.view_all',
                'queues.create',
                'queues.edit',
                'queues.delete',
                'queues.manage',
                
                // Paramètres
                'settings.manage',
                'settings.view',
                'settings.edit',
                
                // Statistiques
                'statistics.view',
                'reports.generate',
                
                // Toutes les autres permissions
                '*',
            ],
            self::ADMIN => [
                // Gestion des utilisateurs (limité)
                'users.view',
                'users.create',
                'users.edit',
                
                // Gestion des files d'attente
                'queues.view_all',
                'queues.create',
                'queues.edit',
                'queues.manage',
                
                // Paramètres (limité)
                'settings.view',
                
                // Statistiques
                'statistics.view',
                'reports.generate',
            ],
            self::AGENT => [
                // Gestion des files d'attente (limité)
                'queues.view_assigned',
                'tickets.create',
                'tickets.edit',
                'tickets.close',
                'tickets.reassign',
                
                // Vue limitée
                'dashboard.view',
            ],
        };
    }
}
