# Refonte de la page de détail d'une file d'attente

## Objectif
Améliorer l'expérience utilisateur et l'interface de la page de gestion des files d'attente en la rendant plus intuitive, réactive et visuellement cohérente.

## Structure actuelle

### Fichiers concernés
1. `resources/views/admin/queues/show.blade.php` (partie non-Livewire)
2. `resources/views/livewire/admin/queue-tickets.blade.php` (composant Livewire)
3. `app/Livewire/Admin/QueueTickets.php` (logique Livewire)

### Problèmes identifiés
- Séparation entre parties Livewire et non-Livewire peu claire
- Boutons d'administration peu visibles en bas de page
- Manque d'informations clés sur la file
- Interface peu réactive sur mobile
- Style incohérent entre les différents éléments

## Plan de refonte

### 1. En-tête amélioré (show.blade.php)
```blade
<div class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    {{ $queue->name }}
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $queue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $queue->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </h2>
                <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" ...>
                        Créée le {{ $queue->created_at->format('d/m/Y') }}
                    </div>
                    @if($queue->creator)
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" ...>
                        Par {{ $queue->creator->name }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                @if($canManage)
                    <a href="{{ route('admin.queues.edit', $queue) }}" class="btn btn-yellow">
                        <i class="fas fa-edit mr-2"></i>Modifier
                    </a>
                    <a href="{{ route('admin.queues.permissions', $queue) }}" class="btn btn-blue">
                        <i class="fas fa-users-cog mr-2"></i>Accès
                    </a>
                @endif
                @if($canDelete)
                    <form action="{{ route('admin.queues.destroy', $queue) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-red" 
                                onclick="return confirm('Êtes-vous sûr ?')">
                            <i class="fas fa-trash mr-2"></i>Supprimer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
```

### 2. Section Statistiques & QR Code (queue-tickets.blade.php)
- Créer une grille responsive avec les indicateurs clés
- Intégrer le QR code dans une carte latérale
- Ajouter des indicateurs visuels de tendance

### 3. Ticket en cours
- Mise en avant avec fond coloré selon le statut
- Actions principales en évidence
- Compteur de temps d'attente

### 4. Tableau des tickets
- Ajout d'une barre de recherche
- Filtres rapides par statut
- Pagination améliorée
- Tri sur les colonnes

### 5. Styles CSS
- Variables pour les couleurs principales
- Classes utilitaires pour les boutons
- Espacements cohérents
- Responsive design

## Étapes d'implémentation

1. **Préparation**
   - [ ] Créer un backup des fichiers actuels
   - [ ] Mettre à jour les dépendances si nécessaire
   - [ ] Créer une branche Git pour la refonte

2. **Implémentation**
   - [ ] Mettre à jour l'en-tête (show.blade.php)
   - [ ] Refactoriser le composant Livewire
   - [ ] Ajouter les nouvelles fonctionnalités
   - [ ] Tester sur différentes tailles d'écran

3. **Tests**
   - [ ] Tester toutes les fonctionnalités existantes
   - [ ] Vérifier le responsive
   - [ ] Tester les performances

4. **Déploiement**
   - [ ] Fusionner dans la branche principale
   - [ ] Mettre à jour la documentation
   - [ ] Former les utilisateurs si nécessaire

## Notes techniques
- Utiliser Tailwind CSS pour le style
- S'assurer de la compatibilité avec les navigateurs récents
- Optimiser les requêtes SQL
- Mettre en cache les données statiques
