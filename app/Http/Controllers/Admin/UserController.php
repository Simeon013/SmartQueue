<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
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
        $users = User::with('roles')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
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
            'roles' => 'array|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assigner les rôles
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$user->name}' créé avec succès. Un email de bienvenue a été envoyé.");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'queuePermissions.queue']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array|exists:roles,id',
        ]);

        $oldRoles = $user->roles->pluck('id')->toArray();
        $newRoles = $validated['roles'] ?? [];

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Mettre à jour le mot de passe si fourni
        if (isset($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Synchroniser les rôles
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
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
        // Empêcher la suppression de l'utilisateur connecté
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $userName = $user->name;
        $userEmail = $user->email;



        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$userName}' supprimé avec succès.");
    }

    /**
     * Afficher les permissions d'un utilisateur.
     */
    public function permissions(User $user)
    {
        $user->load(['roles.permissions', 'queuePermissions.queue']);
        $allPermissions = \App\Models\Permission::all();
        $allQueues = \App\Models\Queue::all();

        return view('admin.users.permissions', compact('user', 'allPermissions', 'allQueues'));
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
