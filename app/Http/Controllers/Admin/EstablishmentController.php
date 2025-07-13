<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstablishmentController extends Controller
{
    public function index()
    {
        // Vérifier les permissions pour voir les établissements
        if (!Auth::user()->can('view', Establishment::class)) {
            abort(403, 'Vous n\'avez pas la permission de voir les établissements.');
        }

        $establishments = Establishment::withCount('queues')->paginate(10);
        return view('admin.establishments.index', compact('establishments'));
    }

    public function create()
    {
        // Vérifier les permissions pour créer des établissements
        if (!Auth::user()->can('create', Establishment::class)) {
            abort(403, 'Vous n\'avez pas la permission de créer des établissements.');
        }

        return view('admin.establishments.create');
    }

    public function store(Request $request)
    {
        // Vérifier les permissions pour créer des établissements
        if (!Auth::user()->can('create', Establishment::class)) {
            abort(403, 'Vous n\'avez pas la permission de créer des établissements.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
        ]);

        Establishment::create($validated);

        return redirect()
            ->route('admin.establishments.index')
            ->with('success', 'Établissement créé avec succès.');
    }

    public function edit(Establishment $establishment)
    {
        // Vérifier les permissions pour modifier cet établissement
        if (!Auth::user()->can('update', $establishment)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cet établissement.');
        }

        return view('admin.establishments.edit', compact('establishment'));
    }

    public function update(Request $request, Establishment $establishment)
    {
        // Vérifier les permissions pour modifier cet établissement
        if (!Auth::user()->can('update', $establishment)) {
            abort(403, 'Vous n\'avez pas la permission de modifier cet établissement.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
        ]);

        $establishment->update($validated);

        return redirect()
            ->route('admin.establishments.index')
            ->with('success', 'Établissement mis à jour avec succès.');
    }

    public function destroy(Establishment $establishment)
    {
        // Vérifier les permissions pour supprimer cet établissement
        if (!Auth::user()->can('delete', $establishment)) {
            abort(403, 'Vous n\'avez pas la permission de supprimer cet établissement.');
        }

        if ($establishment->queues()->exists()) {
            return redirect()
                ->route('admin.establishments.index')
                ->with('error', 'Impossible de supprimer l\'établissement car il contient des files d\'attente.');
        }

        $establishment->delete();

        return redirect()
            ->route('admin.establishments.index')
            ->with('success', 'Établissement supprimé avec succès.');
    }

    public function settings()
    {
        // Vérifier les permissions pour accéder aux paramètres
        if (!Auth::user()->can('manage_settings')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder aux paramètres.');
        }

        $establishment = \App\Models\Establishment::first();
        return view('admin.establishments.settings', compact('establishment'));
    }

    public function updateSettings(Request $request)
    {
        // Vérifier les permissions pour modifier les paramètres
        if (!Auth::user()->can('manage_settings')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les paramètres.');
        }

        $establishment = \App\Models\Establishment::first();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:255',
        ]);
        $establishment->update($validated);
        return redirect()->route('admin.establishment.settings')->with('success', 'Informations de l\'établissement mises à jour.');
    }
}
