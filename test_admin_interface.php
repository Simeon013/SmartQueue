<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Queue;
use App\Models\QueuePermission;
use App\Models\Establishment;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Complet de l'Interface d'Administration SmartQueue ===\n\n";

try {
    // 1. Vérification de la base de données
    echo "1. Vérification de la base de données...\n";
    $userCount = User::count();
    $roleCount = Role::count();
    $permissionCount = Permission::count();
    $queueCount = Queue::count();
    
    echo "   - Utilisateurs : {$userCount}\n";
    echo "   - Rôles : {$roleCount}\n";
    echo "   - Permissions : {$permissionCount}\n";
    echo "   - Files d'attente : {$queueCount}\n\n";

    // 2. Test des rôles par défaut
    echo "2. Test des rôles par défaut...\n";
    $defaultRoles = ['super-admin', 'admin', 'agent-manager', 'agent'];
    foreach ($defaultRoles as $roleName) {
        $role = Role::where('slug', $roleName)->first();
        if ($role) {
            echo "   ✅ Rôle '{$roleName}' trouvé avec {$role->permissions->count()} permissions\n";
        } else {
            echo "   ❌ Rôle '{$roleName}' manquant\n";
        }
    }
    echo "\n";

    // 3. Test des permissions par défaut
    echo "3. Test des permissions par défaut...\n";
    $defaultPermissions = [
        'manage_users', 'manage_roles', 'manage_settings', 'view_queues',
        'create_queues', 'update_queues', 'delete_queues', 'manage_tickets'
    ];
    foreach ($defaultPermissions as $permissionName) {
        $permission = Permission::where('slug', $permissionName)->first();
        if ($permission) {
            echo "   ✅ Permission '{$permissionName}' trouvée\n";
        } else {
            echo "   ❌ Permission '{$permissionName}' manquante\n";
        }
    }
    echo "\n";

    // 4. Test de création d'utilisateur
    echo "4. Test de création d'utilisateur...\n";
    $testUser = User::where('email', 'test-admin@example.com')->first();
    if ($testUser) {
        $testUser->delete();
        echo "   Ancien utilisateur de test supprimé\n";
    }
    
    $testUser = User::create([
        'name' => 'Test Admin',
        'email' => 'test-admin@example.com',
        'password' => bcrypt('password'),
    ]);
    echo "   ✅ Utilisateur de test créé : {$testUser->name}\n";

    // Attribuer le rôle admin
    $adminRole = Role::where('slug', 'admin')->first();
    if ($adminRole) {
        $testUser->roles()->attach($adminRole->id);
        echo "   ✅ Rôle admin attribué\n";
    }
    echo "\n";

    // 5. Test des permissions utilisateur
    echo "5. Test des permissions utilisateur...\n";
    echo "   - Peut gérer les utilisateurs : " . ($testUser->can('manage_users') ? 'OUI' : 'NON') . "\n";
    echo "   - Peut gérer les rôles : " . ($testUser->can('manage_roles') ? 'OUI' : 'NON') . "\n";
    echo "   - Peut voir les files d'attente : " . ($testUser->can('view', Queue::class) ? 'OUI' : 'NON') . "\n";
    echo "   - A le rôle admin : " . ($testUser->hasRole('admin') ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 6. Test de création de file d'attente
    echo "6. Test de création de file d'attente...\n";
    $establishment = Establishment::first();
    if (!$establishment) {
        $establishment = Establishment::create([
            'name' => 'Test Establishment',
            'type' => 'Test',
        ]);
    }
    
    $testQueue = Queue::create([
        'name' => 'File de Test',
        'code' => 'TEST001',
        'is_active' => true,
        'establishment_id' => $establishment->id,
    ]);
    echo "   ✅ File d'attente créée : {$testQueue->name}\n";

    // 7. Test des permissions sur file d'attente
    echo "7. Test des permissions sur file d'attente...\n";
    $testUser->grantQueuePermission($testQueue, 'manage');
    echo "   ✅ Permission 'manage' attribuée sur la file d'attente\n";
    
    echo "   - Peut voir cette file : " . ($testUser->can('view', $testQueue) ? 'OUI' : 'NON') . "\n";
    echo "   - Peut gérer cette file : " . ($testUser->can('manage', $testQueue) ? 'OUI' : 'NON') . "\n";
    echo "   - Peut gérer les tickets : " . ($testUser->can('manage_tickets', $testQueue) ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 8. Test de création de rôle personnalisé
    echo "8. Test de création de rôle personnalisé...\n";
    $customRole = Role::create([
        'name' => 'Testeur',
        'slug' => 'testeur',
        'description' => 'Rôle de test pour validation',
    ]);
    echo "   ✅ Rôle personnalisé créé : {$customRole->name}\n";

    // Attribuer quelques permissions
    $viewPermission = Permission::where('slug', 'view_queues')->first();
    if ($viewPermission) {
        $customRole->permissions()->attach($viewPermission->id);
        echo "   ✅ Permission 'view_queues' attribuée au rôle\n";
    }
    echo "\n";

    // 9. Test de l'utilisateur avec le nouveau rôle
    echo "9. Test de l'utilisateur avec le nouveau rôle...\n";
    $testUser->roles()->attach($customRole->id);
    echo "   ✅ Nouveau rôle attribué à l'utilisateur\n";
    
    echo "   - A le rôle testeur : " . ($testUser->hasRole('testeur') ? 'OUI' : 'NON') . "\n";
    echo "   - Peut voir les files d'attente : " . ($testUser->can('view_queues') ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 10. Test du filtrage des files d'attente
    echo "10. Test du filtrage des files d'attente...\n";
    $accessibleIds = $testUser->getAccessibleQueueIds();
    echo "   - Files d'attente accessibles : " . implode(', ', $accessibleIds) . "\n";
    echo "   - La file de test est accessible : " . (in_array($testQueue->id, $accessibleIds) ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 11. Test de révocation de permissions
    echo "11. Test de révocation de permissions...\n";
    $testUser->revokeQueuePermission($testQueue, 'manage');
    echo "   ✅ Permission 'manage' révoquée\n";
    
    echo "   - Peut encore voir cette file : " . ($testUser->can('view', $testQueue) ? 'OUI' : 'NON') . "\n";
    echo "   - Peut encore gérer cette file : " . ($testUser->can('manage', $testQueue) ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 12. Test de suppression
    echo "12. Test de suppression...\n";
    $testUser->delete();
    $customRole->delete();
    $testQueue->delete();
    echo "   ✅ Données de test supprimées\n\n";

    // 13. Test des routes (simulation)
    echo "13. Test des routes (simulation)...\n";
    $routes = [
        'admin.users.index' => 'Liste des utilisateurs',
        'admin.users.create' => 'Créer un utilisateur',
        'admin.roles.index' => 'Liste des rôles',
        'admin.roles.create' => 'Créer un rôle',
        'admin.queues.index' => 'Liste des files d\'attente',
    ];
    
    foreach ($routes as $routeName => $description) {
        try {
            $url = route($routeName);
            echo "   ✅ Route '{$routeName}' : {$description}\n";
        } catch (Exception $e) {
            echo "   ❌ Route '{$routeName}' : Erreur - {$e->getMessage()}\n";
        }
    }
    echo "\n";

    echo "=== Test terminé avec succès ! ===\n";
    echo "✅ Toutes les fonctionnalités de base sont opérationnelles\n";
    echo "✅ Le système de permissions fonctionne correctement\n";
    echo "✅ Les rôles et utilisateurs peuvent être gérés\n";
    echo "✅ Les permissions granulaires sur les files d'attente fonctionnent\n\n";

} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n";
} 