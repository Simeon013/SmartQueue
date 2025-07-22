<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Super admin voit tous les utilisateurs
        if ($user->isSuperAdmin()) {
            $users = User::latest()->paginate(10);
        }
        // Admin ne voit que les agents
        elseif ($user->isAdmin()) {
            $users = User::where('role', UserRole::AGENT->value)
                        ->latest()
                        ->paginate(10);
        }
        // Les autres rôles ne devraient pas accéder à cette page (géré par le middleware)
        else {
            $users = collect();
        }
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = UserRole::cases();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:' . implode(',', array_column(UserRole::cases(), 'value')),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$user->name}' créé avec succès. Un email de bienvenue a été envoyé.");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        // Empêcher un utilisateur de se modifier lui-même
        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas modifier votre propre compte depuis cette interface.');
        }
        
        // Si l'utilisateur est admin, il ne peut pas modifier les autres admins ou super admins
        if ($currentUser->isAdmin() && ($user->isAdmin() || $user->isSuperAdmin())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cet utilisateur.');
        }
        
        $roles = collect(UserRole::cases());
        
        // Si l'utilisateur est admin, il ne peut pas modifier les rôles
        if ($currentUser->isAdmin()) {
            $roles = $roles->filter(fn($role) => $role === UserRole::AGENT);
        }
        
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles->values()->all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Empêcher un utilisateur de se modifier lui-même
        if ($currentUser->id === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas modifier votre propre compte depuis cette interface.');
        }
        
        // Vérifier les permissions
        if ($currentUser->isAdmin() && ($user->isAdmin() || $user->isSuperAdmin())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cet utilisateur.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:' . implode(',', array_column(UserRole::cases(), 'value')),
        ]);
        
        // Si l'utilisateur est admin, il ne peut pas modifier les rôles
        if ($currentUser->isAdmin() && $validated['role'] !== UserRole::AGENT->value) {
            return redirect()->back()
                ->with('error', 'Vous n\'êtes pas autorisé à attribuer ce rôle.');
        }

        $oldRole = $user->role;
        $newRole = UserRole::from($validated['role']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];
        
        // Ne mettre à jour le rôle que si l'utilisateur a la permission
        if ($currentUser->isSuperAdmin()) {
            $updateData['role'] = $newRole;
        } else {
            // Pour les admins, conserver le rôle existant
            $updateData['role'] = $user->role;
        }

        // Mettre à jour le mot de passe si fourni
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Log des modifications de rôle
        if ($currentUser->isSuperAdmin() && $oldRole !== $newRole) {
            activity()
                ->causedBy($currentUser)
                ->performedOn($user)
                ->withProperties([
                    'old_role' => $oldRole?->value,
                    'new_role' => $newRole->value
                ])
                ->log('Rôle modifié');
        }

        $message = "Utilisateur '{$user->name}' mis à jour avec succès.";
        if (isset($validated['password'])) {
            $message .= " Un email de notification a été envoyé.";
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        // Empêcher la suppression de l'utilisateur connecté
        if ($user->id === $currentUser->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        // Vérifier les permissions
        if ($currentUser->isAdmin() && ($user->isAdmin() || $user->isSuperAdmin())) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'êtes pas autorisé à supprimer cet utilisateur.');
        }
        
        // Vérifier que l'utilisateur a le droit de supprimer
        if (!$currentUser->isSuperAdmin() && $currentUser->isAdmin() && $user->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Action non autorisée.');
        }

        $userName = $user->name;
        
        // Journaliser la suppression
        activity()
            ->causedBy($currentUser)
            ->performedOn($user)
            ->log('Utilisateur supprimé');
            
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$userName}' supprimé avec succès.");
    }

    /**
     * Afficher les permissions d'un utilisateur.
     */
    public function permissions(User $user)
    {
        // Charger uniquement les permissions des files d'attente
        $user->load('queuePermissions.queue');
        
        // Récupérer toutes les files d'attente pour le formulaire d'ajout
        $allQueues = \App\Models\Queue::all();
        
        // Récupérer les permissions du rôle de l'utilisateur
        $rolePermissions = $user->role ? $user->role->getPermissions() : [];
        
        return view('admin.users.permissions', [
            'user' => $user,
            'allQueues' => $allQueues,
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Attribuer un rôle à un utilisateur.
     */
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::find($validated['role_id']);
        $user->assignRole($role);



        return redirect()->back()->with('success', "Rôle '{$role->name}' attribué avec succès à {$user->name}.");
    }

    /**
     * Retirer un rôle d'un utilisateur.
     */
    public function removeRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::find($validated['role_id']);
        $user->removeRole($role);



        return redirect()->back()->with('success', "Rôle '{$role->name}' retiré avec succès de {$user->name}.");
    }
}
