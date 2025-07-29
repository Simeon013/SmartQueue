<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ServiceController extends Controller
{
    /**
     * Vérifie si l'utilisateur est un super administrateur.
     * Sinon, renvoie une erreur 403.
     */
    protected function checkSuperAdmin()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Accès non autorisé. Seul un super administrateur peut gérer les services.');
        }
    }
    /**
     * Affiche la liste des services
     */
    public function index()
    {
        try {
            $this->checkSuperAdmin();
            $services = Service::orderBy('position')->get();
            return view('admin.services.index', compact('services'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des services: ' . $e->getMessage());
            Session::flash('error', 'Une erreur est survenue lors de la récupération des services');
            return redirect()->back();
        }
    }

    /**
     * Affiche le formulaire de création d'un service
     */
    public function create()
    {
        $this->checkSuperAdmin();
        return view('admin.services.create');
    }

    /**
     * Enregistre un nouveau service
     */
    public function store(Request $request)
    {
        $this->checkSuperAdmin();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:services,name',
                'description' => 'nullable|string',
                'icon' => 'required|string|max:50',
                'color' => 'required|string|size:7',
                'is_active' => 'boolean',
            ]);

            // Définir la position comme le prochain numéro disponible
            $validated['position'] = Service::max('position') + 1;
            $validated['is_active'] = $validated['is_active'] ?? false;

            Service::create($validated);

            Session::flash('success', 'Service créé avec succès');
            return redirect()->route('admin.services.index');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du service: ' . $e->getMessage());
            Session::flash('error', 'Une erreur est survenue lors de la création du service');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Affiche le formulaire d'édition d'un service
     */
    public function edit(Service $service)
    {
        $this->checkSuperAdmin();
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Met à jour un service existant
     */
    public function update(Request $request, Service $service)
    {
        $this->checkSuperAdmin();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:services,name,' . $service->id,
                'description' => 'nullable|string',
                'icon' => 'required|string|max:50',
                'color' => 'required|string|size:7',
                'is_active' => 'boolean',
            ]);

            $service->update($validated);

            Session::flash('success', 'Service mis à jour avec succès');
            return redirect()->route('admin.services.index');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du service: ' . $e->getMessage());
            Session::flash('error', 'Une erreur est survenue lors de la mise à jour du service');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Supprime un service
     */
    public function destroy(Service $service)
    {
        $this->checkSuperAdmin();

        try {
            $service->delete();
            Session::flash('success', 'Service supprimé avec succès');
            return redirect()->route('admin.services.index');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du service: ' . $e->getMessage());
            Session::flash('error', 'Une erreur est survenue lors de la suppression du service');
            return redirect()->back();
        }
    }

    /**
     * Active ou désactive un service
     */
    public function toggleStatus(Service $service)
    {
        $this->checkSuperAdmin();

        try {
            $service->update(['is_active' => !$service->is_active]);
            return response()->json([
                'success' => true,
                'is_active' => $service->fresh()->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement d\'état du service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du changement d\'état du service'
            ], 500);
        }
    }

    /**
     * Met à jour l'ordre des services
     */
    public function updateOrder(Request $request)
    {
        try {
            $order = $request->input('order');

            foreach ($order as $position => $id) {
                Service::where('id', $id)->update(['position' => $position]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'ordre des services: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue'], 500);
        }
    }
}
