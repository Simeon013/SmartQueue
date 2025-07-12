<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        // Récupérer les paramètres de fermeture automatique
        $autoCloseSettings = SystemSetting::getByGroup('auto_close')->toArray();

        return view('admin.settings.index', compact('autoCloseSettings'));
    }

    public function updateAutoClose(Request $request)
    {
        $autoCloseEnabled = $request->has('auto_close_enabled');

        try {
            // Validation simplifiée
            $validated = $request->validate([
                'auto_close_time' => 'required|date_format:H:i',
                'auto_close_days' => 'required|array',
                'auto_close_days.*' => 'integer|between:0,6'
            ]);

            // Convertir les jours en entiers
            if (isset($validated['auto_close_days'])) {
                $validated['auto_close_days'] = array_map('intval', $validated['auto_close_days']);
            }

            // Sauvegarder les paramètres
            SystemSetting::setValue(
                'auto_close_enabled',
                $autoCloseEnabled,
                'boolean',
                'auto_close',
                'Activer la fermeture automatique des files'
            );

            SystemSetting::setValue(
                'auto_close_time',
                $validated['auto_close_time'],
                'string',
                'auto_close',
                'Heure de fermeture automatique'
            );

            SystemSetting::setValue(
                'auto_close_days',
                json_encode($validated['auto_close_days']),
                'json',
                'auto_close',
                'Jours de fermeture automatique'
            );

            return redirect()->route('admin.settings')
                ->with('success', 'Paramètres de fermeture automatique mis à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('admin.settings')
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('admin.settings')
                ->with('error', 'Erreur lors de la sauvegarde des paramètres.');
        }
    }
}
