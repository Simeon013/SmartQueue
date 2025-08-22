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
        $this->authorize('viewAny', User::class);

        $user = Auth::user();

        // Récupérer les utilisateurs en fonction du rôle de l'utilisateur connecté
        $query = User::query();

        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            // Les administrateurs ne voient que les agents
            $query->where('role', UserRole::AGENT->value);
        }

        // Ajouter des filtres si nécessaire
        if (request()->has('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (request()->has('role') && in_array(request('role'), array_column(UserRole::cases(), 'value'))) {
            $query->where('role', request('role'));
        }

        $users = $query->latest()->paginate(10);

        // Statistiques
        $stats = [
            'total' => User::count(),
            'super_admins' => User::where('role', UserRole::SUPER_ADMIN->value)->count(),
            'admins' => User::where('role', UserRole::ADMIN->value)->count(),
            'agents' => User::where('role', UserRole::AGENT->value)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $user = Auth::user();
        $roles = collect(UserRole::cases());

        // Filtrer les rôles en fonction des permissions de l'utilisateur
        if ($user->isAdmin() && !$user->isSuperAdmin()) {
            $roles = $roles->filter(fn($role) => $role === UserRole::AGENT);
        }

        return view('admin.users.create', [
            'roles' => $roles->values()->all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:' . implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        // Vérifier si l'utilisateur a le droit d'attribuer ce rôle
        $user = Auth::user();
        $role = UserRole::from($validated['role']);

        if ($user->isAdmin() && !$user->isSuperAdmin() && $role !== UserRole::AGENT) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à créer un utilisateur avec ce rôle.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'email_verified_at' => now(),
        ]);

        // Journalisation de l'action
        // activity()
        //     ->causedBy(auth()->user())
        //     ->performedOn($user)
        //     ->withProperties([
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'role' => $role->value,
        //     ])
        //     ->log('Utilisateur créé');

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur '{$user->name}' créé avec succès.");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        // Charger toutes les permissions, y compris les globales
        $allPermissions = $user->getAllQueuePermissions();


        // Séparer les permissions pour les files actives (status = 'open' ou 'waiting')
        $activeQueuePermissions = $allPermissions->where('queue.status', 'open');
        $activeQueuePermissions = $activeQueuePermissions->union($allPermissions->where('queue.status', 'paused'));

        // Ajouter les permissions à l'utilisateur pour la vue
        $user->setRelation('queuePermissions', $activeQueuePermissions);

        return view('admin.users.show', [
            'user' => $user,
            'rolePermissions' => $user->role ? $user->role->getPermissions() : []
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $currentUser = auth()->user();
        $roles = collect(UserRole::cases());

        // Filtrer les rôles en fonction des permissions de l'utilisateur
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
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
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:' . implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        $currentUser = auth()->user();
        $newRole = UserRole::from($validated['role']);
        $oldRole = $user->role;

        // Vérifier si l'utilisateur a le droit d'attribuer ce rôle
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && $newRole !== UserRole::AGENT) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à attribuer ce rôle.');
        }

        // Préparer les données de mise à jour
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // Mettre à jour le rôle si l'utilisateur a la permission
        if ($currentUser->isSuperAdmin()) {
            $updateData['role'] = $newRole;
        }

        // Mettre à jour le mot de passe si fourni
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Journalisation avant la mise à jour
        $changes = [];
        foreach ($updateData as $key => $value) {
            if ($key === 'password') continue; // Ne pas logger le mot de passe
            if ($user->$key != $value) {
                $changes[$key] = [
                    'old' => $user->$key,
                    'new' => $value
                ];
            }
        }

        // Effectuer la mise à jour
        $user->update($updateData);

        // Journalisation des modifications
        if (!empty($changes)) {
            // activity()
            //     ->causedBy($currentUser)
            //     ->performedOn($user)
            //     ->withProperties([
            //         'changes' => $changes,
            //         'old_role' => $oldRole?->value,
            //         'new_role' => $newRole->value
            //     ])
            //     ->log('Utilisateur mis à jour');
        }

        $message = "Utilisateur '{$user->name}' mis à jour avec succès.";
        if (isset($validated['password'])) {
            $message .= " Le mot de passe a été mis à jour.";
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $currentUser = auth()->user();
        $userName = $user->name;

        // Vérifier que l'utilisateur ne se supprime pas lui-même
        if ($user->id === $currentUser->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Journalisation avant suppression
        // activity()
        //     ->causedBy($currentUser)
        //     ->performedOn($user)
        //     ->withProperties([
        //         'name' => $user->name,
        //         'email' => $user->email,
        //         'role' => $user->role->value,
        //         'deleted_at' => now()
        //     ])
        //     ->log('Utilisateur supprimé');

        // Supprimer l'utilisateur
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur '{$userName}' a été supprimé avec succès.");
    }

    /**
     * Afficher les permissions d'un utilisateur.
     */
    public function permissions(User $user)
    {
        $this->authorize('managePermissions', $user);

        // Récupérer toutes les permissions (spécifiques et globales)
        $allPermissions = $user->getAllQueuePermissions();

        // Charger les relations manquantes pour les permissions
        $allPermissions->load([
            'queue.service',
            'grantedBy'
        ]);

        // dd($allPermissions);

        // Séparer les permissions pour les files actives (status = 'open' ou 'waiting')
        $activeQueuePermissions = $allPermissions->where('queue.status', 'open');
        $activeQueuePermissions = $activeQueuePermissions->union($allPermissions->where('queue.status', 'paused'));


        // dd($activeQueuePermissions);

        // Permissions pour les files inactives (tous les autres statuts)
        $inactiveQueuePermissions = $allPermissions->where('queue.status', '!=', 'open');

        // Récupérer les IDs des files avec des permissions globales
        $globalQueueIds = \App\Models\QueuePermission::whereNull('user_id')
            ->pluck('queue_id')
            ->unique()
            ->values();

        // Récupérer toutes les files d'attente pour le formulaire d'ajout
        $allQueues = \App\Models\Queue::with('service')
            ->orderBy('name')
            ->get();

        // Récupérer les permissions du rôle de l'utilisateur
        $rolePermissions = $user->role ? $user->role->getPermissions() : [];

        // Statistiques des permissions
        $stats = [
            'total_permissions' => $allPermissions->count(),
            'manager_permissions' => $allPermissions->where('permission_type', 'manager')->count(),
            'operator_permissions' => $allPermissions->where('permission_type', 'operator')->count(),
            'global_permissions' => $allPermissions->where('is_global', true)->count(),
        ];

        // Ajouter les permissions au modèle user pour la vue
        $user->setRelation('queuePermissions', $allPermissions);

        // Préparer les données pour le DataTable
        $queuesDataTable = $allPermissions->map(function($permission) use ($user) {
            $queue = $permission->queue;
            $service = $queue ? $queue->service : null;

            return [
                'id' => $permission->id,
                'queue_id' => $permission->queue_id,
                'queue_name' => $queue ? $queue->name : 'N/A',
                'queue_code' => $queue ? $queue->code : 'N/A',
                'service_name' => $service ? $service->name : 'N/A',
                'permission_type' => $permission->permission_type,
                'permission_label' => $permission->permission_type === 'manager' ? 'Gestion complète' : 'Gestion des tickets',
                'is_global' => (bool)($permission->is_global ?? false),
                'created_at' => $permission->created_at->format('d/m/Y H:i'),
                'granted_by' => $permission->grantedBy ? $permission->grantedBy->name : 'Système',
                'status' => $queue ? $queue->status : 'unknown',
                'can_delete' => !$permission->is_global && $user->can('delete', $permission)
            ];
        })->values();

        return view('admin.users.permissions', [
            'user' => $user,
            'allQueues' => $allQueues,
            'globalQueueIds' => $globalQueueIds,
            'rolePermissions' => $rolePermissions,
            'stats' => $stats,
            'activeQueuePermissions' => $activeQueuePermissions,
            'inactiveQueuePermissions' => $inactiveQueuePermissions,
            'queuesDataTable' => $queuesDataTable
        ]);
    }

    /**
     * Attribuer un rôle à un utilisateur.
     */
    public function assignRole(Request $request, User $user)
    {
        $this->authorize('assignRole', $user);

        $validated = $request->validate([
            'role' => ['required', 'in:' . implode(',', array_column(UserRole::cases(), 'value'))],
        ]);

        $newRole = UserRole::from($validated['role']);
        $oldRole = $user->role;

        // Vérifier que l'utilisateur a le droit d'attribuer ce rôle
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && $newRole !== UserRole::AGENT) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à attribuer ce rôle.');
        }

        // Mettre à jour le rôle
        $user->update(['role' => $newRole]);

        // Journalisation
        // activity()
        //     ->causedBy(auth()->user())
        //     ->performedOn($user)
        //     ->withProperties([
        //         'old_role' => $oldRole?->value,
        //         'new_role' => $newRole->value
        //     ])
        //     ->log('Rôle attribué');

        return redirect()->back()
            ->with('success', "Le rôle a été mis à jour avec succès pour l'utilisateur '{$user->name}'.");
    }
}
