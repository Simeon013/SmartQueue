<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Queue;
use App\Models\QueuePermission;

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test du Système de Permissions SmartQueue ===\n\n";

try {
    // 1. Vérifier que les rôles existent
    echo "1. Vérification des rôles...\n";
    $roles = Role::all();
    echo "Rôles trouvés : " . $roles->count() . "\n";
    foreach ($roles as $role) {
        echo "  - {$role->name}: {$role->description}\n";
    }
    echo "\n";

    // 2. Vérifier que les permissions existent
    echo "2. Vérification des permissions...\n";
    $permissions = Permission::all();
    echo "Permissions trouvées : " . $permissions->count() . "\n";
    foreach ($permissions as $permission) {
        echo "  - {$permission->name}: {$permission->description}\n";
    }
    echo "\n";

    // 3. Créer un utilisateur de test (supprimer s'il existe déjà)
    echo "3. Création d'un utilisateur de test...\n";
    $existingUser = User::where('email', 'test@example.com')->first();
    if ($existingUser) {
        $existingUser->delete();
        echo "Ancien utilisateur 'test@example.com' supprimé.\n";
    }
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    echo "Utilisateur créé : {$user->name} ({$user->email})\n\n";

    // 4. Attribuer un rôle
    echo "4. Attribution d'un rôle...\n";
    $agentRole = Role::where('name', 'agent')->first();
    if ($agentRole) {
        $user->roles()->attach($agentRole->id);
        echo "Rôle 'agent' attribué à l'utilisateur\n";
    } else {
        echo "ERREUR: Rôle 'agent' non trouvé\n";
    }
    echo "\n";

    // 5. Créer une file d'attente de test
    echo "5. Création d'une file d'attente de test...\n";
    $establishment = \App\Models\Establishment::first();
    if (!$establishment) {
        $establishment = \App\Models\Establishment::create([
            'name' => 'Test Establishment',
            'type' => 'Test',
        ]);
    }

    $queue = Queue::create([
        'name' => 'Test Queue',
        'code' => 'TEST123',
        'is_active' => true,
        'establishment_id' => $establishment->id,
    ]);
    echo "File d'attente créée : {$queue->name}\n\n";

    // 6. Tester les permissions
    echo "6. Test des permissions...\n";

    // Vérifier les rôles
    echo "L'utilisateur a le rôle 'agent' : " . ($user->hasRole('agent') ? 'OUI' : 'NON') . "\n";
    echo "L'utilisateur a le rôle 'admin' : " . ($user->hasRole('admin') ? 'OUI' : 'NON') . "\n";

    // Vérifier les permissions globales
    echo "L'utilisateur peut gérer les utilisateurs : " . ($user->can('manage_users') ? 'OUI' : 'NON') . "\n";
    echo "L'utilisateur peut voir les files d'attente : " . ($user->can('view', Queue::class) ? 'OUI' : 'NON') . "\n";

    // Vérifier les permissions sur la file d'attente spécifique
    echo "L'utilisateur peut voir cette file d'attente : " . ($user->can('view', $queue) ? 'OUI' : 'NON') . "\n";
    echo "L'utilisateur peut gérer cette file d'attente : " . ($user->can('manage', $queue) ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 7. Attribuer une permission spécifique
    echo "7. Attribution d'une permission spécifique...\n";
    $user->grantQueuePermission($queue, 'view');
    echo "Permission 'view' attribuée sur la file d'attente\n";

    // Vérifier à nouveau
    echo "L'utilisateur peut voir cette file d'attente : " . ($user->can('view', $queue) ? 'OUI' : 'NON') . "\n";
    echo "L'utilisateur peut gérer cette file d'attente : " . ($user->can('manage', $queue) ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 8. Tester le filtrage
    echo "8. Test du filtrage des files d'attente...\n";
    $accessibleIds = $user->getAccessibleQueueIds();
    echo "Files d'attente accessibles : " . implode(', ', $accessibleIds) . "\n";
    echo "La file d'attente de test est accessible : " . (in_array($queue->id, $accessibleIds) ? 'OUI' : 'NON') . "\n";
    echo "\n";

    // 9. Nettoyage
    echo "9. Nettoyage...\n";
    $user->delete();
    $queue->delete();
    echo "Données de test supprimées\n\n";

    echo "=== Test terminé avec succès ! ===\n";

} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n";
}
