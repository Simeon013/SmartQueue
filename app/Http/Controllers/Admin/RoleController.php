<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->latest()->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        $permissionsByGroup = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->slug)[0];
        });

        return view('admin.roles.create', compact('permissions', 'permissionsByGroup'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'array|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        // Assigner les permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $permissionsByGroup = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->slug)[0];
        });

        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions', 'permissionsByGroup'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'array|exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        // Synchroniser les permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Empêcher la suppression des rôles système
        if (in_array($role->slug, ['super-admin', 'admin', 'agent-manager', 'agent'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Ce rôle système ne peut pas être supprimé.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }

    /**
     * Afficher les utilisateurs ayant ce rôle.
     */
    public function users(Role $role)
    {
        $role->load('users');
        return view('admin.roles.users', compact('role'));
    }

    /**
     * Attribuer une permission à un rôle.
     */
    public function assignPermission(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::find($validated['permission_id']);
        $role->assignPermission($permission);

        return redirect()->back()->with('success', "Permission '{$permission->name}' attribuée avec succès.");
    }

    /**
     * Retirer une permission d'un rôle.
     */
    public function removePermission(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::find($validated['permission_id']);
        $role->removePermission($permission);

        return redirect()->back()->with('success', "Permission '{$permission->name}' retirée avec succès.");
    }
}
