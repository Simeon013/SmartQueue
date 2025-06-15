<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;

class EstablishmentController extends Controller
{
    public function index()
    {
        $establishments = Establishment::withCount('queues')->paginate(10);
        return view('admin.establishments.index', compact('establishments'));
    }

    public function create()
    {
        return view('admin.establishments.create');
    }

    public function store(Request $request)
    {
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
        return view('admin.establishments.edit', compact('establishment'));
    }

    public function update(Request $request, Establishment $establishment)
    {
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
}
