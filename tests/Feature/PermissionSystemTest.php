<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Queue;
use App\Models\QueuePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_all_permissions()
    {
        // Créer les permissions et rôles
        $this->seed();

        $superAdmin = User::factory()->create();
        $superAdminRole = Role::where('name', 'super-admin')->first();
        $superAdmin->roles()->attach($superAdminRole->id);

        // Vérifier que le super admin a toutes les permissions
        $this->assertTrue($superAdmin->can('manage_users'));
        $this->assertTrue($superAdmin->can('manage_roles'));
        $this->assertTrue($superAdmin->can('manage_settings'));
        $this->assertTrue($superAdmin->can('view', Queue::class));
        $this->assertTrue($superAdmin->can('create', Queue::class));
    }

    public function test_agent_can_only_access_assigned_queues()
    {
        // Créer les permissions et rôles
        $this->seed();

        $agent = User::factory()->create();
        $agentRole = Role::where('name', 'agent')->first();
        $agent->roles()->attach($agentRole->id);

        // Créer deux files d'attente
        $queue1 = Queue::factory()->create(['name' => 'Queue 1']);
        $queue2 = Queue::factory()->create(['name' => 'Queue 2']);

        // Donner accès seulement à la première file d'attente
        $agent->grantQueuePermission($queue1->id, 'view');

        // Vérifier les permissions
        $this->assertTrue($agent->can('view', $queue1));
        $this->assertFalse($agent->can('view', $queue2));
        $this->assertFalse($agent->can('manage', $queue1));
        $this->assertFalse($agent->can('manage', $queue2));

        // Vérifier les IDs des files d'attente accessibles
        $accessibleIds = $agent->getAccessibleQueueIds();
        $this->assertContains($queue1->id, $accessibleIds);
        $this->assertNotContains($queue2->id, $accessibleIds);
    }

    public function test_queue_manager_can_manage_assigned_queues()
    {
        // Créer les permissions et rôles
        $this->seed();

        $manager = User::factory()->create();
        $managerRole = Role::where('name', 'agent-manager')->first();
        $manager->roles()->attach($managerRole->id);

        // Créer une file d'attente
        $queue = Queue::factory()->create(['name' => 'Test Queue']);

        // Donner les permissions de gestion
        $manager->grantQueuePermission($queue->id, 'manage');

        // Vérifier les permissions
        $this->assertTrue($manager->can('view', $queue));
        $this->assertTrue($manager->can('update', $queue));
        $this->assertTrue($manager->can('delete', $queue));
        $this->assertTrue($manager->can('manage_tickets', $queue));
    }

    public function test_user_can_have_multiple_roles()
    {
        // Créer les permissions et rôles
        $this->seed();

        $user = User::factory()->create();
        $agentRole = Role::where('name', 'agent')->first();
        $adminRole = Role::where('name', 'admin')->first();

        // Attacher les deux rôles
        $user->roles()->attach([$agentRole->id, $adminRole->id]);

        // Vérifier que l'utilisateur a les permissions des deux rôles
        $this->assertTrue($user->hasRole('agent'));
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->can('manage_users')); // Permission d'admin
        $this->assertTrue($user->can('view', Queue::class)); // Permission d'agent
    }

    public function test_queue_permission_granting_and_revoking()
    {
        // Créer les permissions et rôles
        $this->seed();

        $user = User::factory()->create();
        $queue = Queue::factory()->create();

        // Tester l'attribution de permission
        $user->grantQueuePermission($queue->id, 'view');
        $this->assertTrue($user->can('view', $queue));

        // Tester la révocation de permission
        $user->revokeQueuePermission($queue->id, 'view');
        $this->assertFalse($user->can('view', $queue));
    }

    public function test_permission_inheritance()
    {
        // Créer les permissions et rôles
        $this->seed();

        $user = User::factory()->create();
        $queue = Queue::factory()->create();

        // Donner la permission 'manage' qui inclut 'view'
        $user->grantQueuePermission($queue->id, 'manage');

        // Vérifier que l'utilisateur a toutes les permissions inférieures
        $this->assertTrue($user->can('view', $queue));
        $this->assertTrue($user->can('update', $queue));
        $this->assertTrue($user->can('delete', $queue));
        $this->assertTrue($user->can('manage_tickets', $queue));
    }

    public function test_role_permission_assignment()
    {
        // Créer les permissions et rôles
        $this->seed();

        $role = Role::factory()->create(['name' => 'test-role']);
        $permission = Permission::factory()->create(['name' => 'test-permission']);

        // Attacher la permission au rôle
        $role->permissions()->attach($permission->id);

        // Vérifier que le rôle a la permission
        $this->assertTrue($role->permissions->contains($permission));

        // Créer un utilisateur avec ce rôle
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        // Vérifier que l'utilisateur a la permission via son rôle
        $this->assertTrue($user->can('test-permission'));
    }
}
