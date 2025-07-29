<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si le service par défaut existe déjà
        if (!Service::where('name', 'Général')->exists()) {
            Service::create([
                'name' => 'Général',
                'description' => 'Service par défaut pour les files d\'attente sans service spécifique',
                'icon' => 'fa-solid fa-list',
                'color' => '#3b82f6', // Couleur bleue par défaut
                'is_active' => true,
                'position' => 0, // Position 0 pour le mettre en premier
            ]);
            
            $this->command->info('Service par défaut "Général" créé avec succès.');
        } else {
            $this->command->info('Le service par défaut "Général" existe déjà.');
        }
    }
}
