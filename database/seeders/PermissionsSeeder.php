<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Permissions système
            [
                'name' => 'Gérer les utilisateurs',
                'slug' => 'users.manage',
                'description' => 'Peut créer, modifier et supprimer des utilisateurs'
            ],
            [
                'name' => 'Gérer les rôles',
                'slug' => 'roles.manage',
                'description' => 'Peut créer, modifier et supprimer des rôles'
            ],
            [
                'name' => 'Gérer les paramètres',
                'slug' => 'settings.manage',
                'description' => 'Peut modifier les paramètres de l\'application'
            ],
            [
                'name' => 'Gérer les établissements',
                'slug' => 'establishments.manage',
                'description' => 'Peut créer, modifier et supprimer des établissements'
            ],

            // Permissions sur les files d'attente
            [
                'name' => 'Créer des files d\'attente',
                'slug' => 'queues.create',
                'description' => 'Peut créer de nouvelles files d\'attente'
            ],
            [
                'name' => 'Voir les files d\'attente',
                'slug' => 'queues.view',
                'description' => 'Peut voir les files d\'attente'
            ],
            [
                'name' => 'Modifier les files d\'attente',
                'slug' => 'queues.edit',
                'description' => 'Peut modifier les files d\'attente'
            ],
            [
                'name' => 'Supprimer les files d\'attente',
                'slug' => 'queues.delete',
                'description' => 'Peut supprimer les files d\'attente'
            ],
            [
                'name' => 'Gérer les permissions des files',
                'slug' => 'queues.manage_permissions',
                'description' => 'Peut gérer les permissions sur les files d\'attente'
            ],

            // Permissions sur les tickets
            [
                'name' => 'Gérer les tickets',
                'slug' => 'tickets.manage',
                'description' => 'Peut gérer tous les aspects des tickets'
            ],
            [
                'name' => 'Créer des tickets',
                'slug' => 'tickets.create',
                'description' => 'Peut créer des tickets manuellement'
            ],
            [
                'name' => 'Modifier des tickets',
                'slug' => 'tickets.edit',
                'description' => 'Peut modifier les tickets'
            ],
            [
                'name' => 'Supprimer des tickets',
                'slug' => 'tickets.delete',
                'description' => 'Peut supprimer des tickets'
            ],
            [
                'name' => 'Appeler des tickets',
                'slug' => 'tickets.call',
                'description' => 'Peut appeler les tickets suivants'
            ],
            [
                'name' => 'Marquer la présence',
                'slug' => 'tickets.mark_present',
                'description' => 'Peut marquer un ticket comme présent'
            ],
            [
                'name' => 'Passer des tickets',
                'slug' => 'tickets.skip',
                'description' => 'Peut passer un ticket'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('Permissions créées avec succès !');
    }
}
