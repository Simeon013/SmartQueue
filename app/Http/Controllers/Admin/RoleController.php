<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer les rôles statiques depuis l'énumération
        $roles = collect(UserRole::cases())->map(function ($role) {
            return (object) [
                'name' => $role->label(),
                'slug' => $role->value,
                'description' => $role->label(), // Utilisation de label() comme description pour le moment
                'permissions' => collect($role->getPermissions())
            ];
        });

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Afficher les détails d'un rôle
     */
    public function show(string $roleSlug)
    {
        $role = UserRole::tryFrom($roleSlug);
        
        if (!$role) {
            abort(404, 'Rôle non trouvé');
        }

        $roleData = (object) [
            'name' => $role->label(),
            'slug' => $role->value,
            'description' => $role->label(), // Utilisation de label() comme description pour le moment
            'permissions' => collect($role->getPermissions())
        ];

        return view('admin.roles.show', compact('roleData'));
    }

    /**
     * Les méthodes suivantes ne sont plus utilisées car les rôles sont maintenant gérés de manière statique
     * mais sont conservées pour éviter les erreurs de routage
     */
    public function create()
    {
        return redirect()->route('admin.roles.index')
            ->with('info', 'La création de rôles est désactivée. Les rôles sont maintenant gérés de manière statique.');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.roles.index')
            ->with('info', 'La création de rôles est désactivée. Les rôles sont maintenant gérés de manière statique.');
    }

    public function edit(string $roleSlug)
    {
        return redirect()->route('admin.roles.show', $roleSlug)
            ->with('info', 'La modification des rôles est désactivée. Les rôles sont maintenant gérés de manière statique.');
    }

    public function update(Request $request, string $roleSlug)
    {
        return redirect()->route('admin.roles.show', $roleSlug)
            ->with('info', 'La modification des rôles est désactivée. Les rôles sont maintenant gérés de manière statique.');
    }

    public function destroy(string $roleSlug)
    {
        return redirect()->route('admin.roles.index')
            ->with('info', 'La suppression des rôles est désactivée. Les rôles sont maintenant gérés de manière statique.');
    }

    /**
     * Afficher les utilisateurs ayant ce rôle.
     */
    public function users(string $roleSlug)
    {
        // Vérifier si le slug correspond à une valeur valide de l'énumération
        $role = null;
        foreach (UserRole::cases() as $case) {
            if ($case->value === $roleSlug) {
                $role = $case;
                break;
            }
        }
        
        if (!$role) {
            abort(404, 'Rôle non trouvé');
        }
        
        return view('admin.roles.users', [
            'roleSlug' => $roleSlug,
            'role' => (object) [
                'name' => $role->label(),
                'slug' => $role->value,
                'description' => $role->getDescription()
            ]
        ]);
    }

    /**
     * Attribuer une permission à un rôle.
     * Cette méthode est désactivée car les permissions sont maintenant gérées de manière statique.
     */
    public function assignPermission(Request $request, string $roleSlug)
    {
        $role = UserRole::tryFrom($roleSlug);
        
        if (!$role) {
            abort(404, 'Rôle non trouvé');
        }
        
        return redirect()
            ->route('admin.roles.show', $roleSlug)
            ->with('info', 'La gestion des permissions est maintenant effectuée de manière statique dans le code.');
    }

    /**
     * Retirer une permission d'un rôle.
     * Cette méthode est désactivée car les permissions sont maintenant gérées de manière statique.
     */
    public function removePermission(Request $request, string $roleSlug)
    {
        $role = UserRole::tryFrom($roleSlug);
        
        if (!$role) {
            abort(404, 'Rôle non trouvé');
        }
        
        return redirect()
            ->route('admin.roles.show', $roleSlug)
            ->with('info', 'La gestion des permissions est maintenant effectuée de manière statique dans le code.');
    }
}
