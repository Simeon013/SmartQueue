<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer le rôle Super Admin
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Administrateur',
                'description' => 'Accès complet à toutes les fonctionnalités du système'
            ]
        );

        // Créer le rôle Admin
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrateur',
                'description' => 'Gestion des utilisateurs et des files d\'attente de son établissement'
            ]
        );

        // Créer le rôle Agent Manager
        $agentManager = Role::updateOrCreate(
            ['slug' => 'agent-manager'],
            [
                'name' => 'Agent Manager',
                'description' => 'Peut créer des files et gérer les permissions sur ses files'
            ]
        );

        // Créer le rôle Agent
        $agent = Role::updateOrCreate(
            ['slug' => 'agent'],
            [
                'name' => 'Agent',
                'description' => 'Gestion des tickets sur les files autorisées'
            ]
        );

        // Assigner les permissions au Super Admin (toutes les permissions)
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // Assigner les permissions à l'Admin
        $adminPermissions = Permission::whereIn('slug', [
            'users.manage',
            'establishments.manage',
            'queues.create',
            'queues.view',
            'queues.edit',
            'queues.delete',
            'queues.manage_permissions',
            'tickets.manage',
            'tickets.create',
            'tickets.edit',
            'tickets.delete',
            'tickets.call',
            'tickets.mark_present',
            'tickets.skip'
        ])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));

        // Assigner les permissions à l'Agent Manager
        $agentManagerPermissions = Permission::whereIn('slug', [
            'queues.create',
            'queues.view',
            'queues.edit',
            'queues.manage_permissions',
            'tickets.manage',
            'tickets.create',
            'tickets.edit',
            'tickets.delete',
            'tickets.call',
            'tickets.mark_present',
            'tickets.skip'
        ])->get();
        $agentManager->permissions()->sync($agentManagerPermissions->pluck('id'));

        // Assigner les permissions à l'Agent
        $agentPermissions = Permission::whereIn('slug', [
            'queues.view',
            'tickets.call',
            'tickets.mark_present',
            'tickets.skip'
        ])->get();
        $agent->permissions()->sync($agentPermissions->pluck('id'));

        $this->command->info('Rôles créés avec succès !');
    }
}
