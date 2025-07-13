<?php

require_once 'vendor/autoload.php';

// Initialiser Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Vérification des Permissions ===\n\n";

// 1. Vérifier les permissions dans la base
echo "1. Permissions dans la base de données :\n";
$permissions = \App\Models\Permission::all();
foreach ($permissions as $permission) {
    echo "   - {$permission->slug} ({$permission->name})\n";
}
echo "\n";

// 2. Vérifier les rôles et leurs permissions
echo "2. Rôles et leurs permissions :\n";
$roles = \App\Models\Role::all();
foreach ($roles as $role) {
    echo "   Rôle : {$role->name} ({$role->slug})\n";
    $rolePermissions = $role->permissions;
    if ($rolePermissions->count() > 0) {
        foreach ($rolePermissions as $permission) {
            echo "     - {$permission->slug}\n";
        }
    } else {
        echo "     - Aucune permission\n";
    }
    echo "\n";
}

// 3. Vérifier les utilisateurs et leurs rôles
echo "3. Utilisateurs et leurs rôles :\n";
$users = \App\Models\User::all();
foreach ($users as $user) {
    echo "   Utilisateur : {$user->name} ({$user->email})\n";
    $userRoles = $user->roles;
    if ($userRoles->count() > 0) {
        foreach ($userRoles as $role) {
            echo "     - Rôle : {$role->name} ({$role->slug})\n";
        }
    } else {
        echo "     - Aucun rôle\n";
    }
    echo "\n";
}

echo "=== Fin de la vérification ===\n"; 