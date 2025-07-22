# Plan de Gestion des Rôles et Permissions

## 1. Objectifs
- [x] Mettre en place un système de 3 rôles fixes : Super Admin, Admin, Agent
- [x] Définir des permissions précises pour chaque rôle
- [x] Implémenter la gestion des accès aux files d'attente
- [x] Masquer les boutons non autorisés dans l'interface utilisateur
- [ ] Vérifier et corriger les écarts entre les règles métiers et l'implémentation

## 2. Rôles et Permissions

### 2.1 Super Admin
- **Accès complet** à toutes les fonctionnalités
- Peut gérer tous les utilisateurs (Super Admins, Admins, Agents)
- Peut modifier les paramètres système
- Accès illimité à toutes les files d'attente
- Peut attribuer n'importe quel rôle aux utilisateurs

### 2.2 Admin
- Peut gérer uniquement les utilisateurs de type Agent
- Ne peut pas créer ou modifier des utilisateurs Admin ou Super Admin
- Ne peut pas modifier le rôle des utilisateurs existants
- Ne peut pas accéder aux paramètres système
- A accès à toutes les files d'attente (même sans permission explicite)
- Peut gérer toutes les files d'attente

### 2.3 Agent
- Peut créer des files d'attente (obtient automatiquement tous les droits sur les files créées)
- Peut gérer uniquement les files pour lesquelles il a une permission explicite
- Ne peut pas gérer les utilisateurs
- Vue limitée aux statistiques des files auxquelles il a accès

## 3. État Actuel et Écarts Identifiés

### 3.1 Règles Implémentées
- [x] Structure de base des 3 rôles (Super Admin, Admin, Agent)
- [x] Système de permissions via l'enum UserRole
- [x] Middleware de vérification des permissions
- [x] Interface de gestion des permissions par utilisateur
- [x] Attribution automatique des droits sur les files créées

### 3.2 Écarts à Corriger
- [ ] **Gestion des utilisateurs**
  - [ ] L'admin peut actuellement voir tous les utilisateurs, il ne devrait voir que les agents
  - [ ] L'admin peut potentiellement modifier le rôle des utilisateurs
  - [ ] L'admin ne devrait pas pouvoir créer de nouveaux admins

- [ ] **Gestion des files d'attente**
  - [ ] Les admins et super admins devraient avoir accès à toutes les files sans restriction
  - [ ] Les agents ne devraient voir que les files pour lesquelles ils ont une permission
  - [ ] Les boutons d'action doivent être masqués en fonction des permissions

- [ ] **Permissions manquantes**
  - [ ] Ajouter une permission pour la gestion des paramètres système
  - [ ] Ajouter des permissions spécifiques pour la création/suppression des files
  - [ ] Implémenter la vérification des permissions dans les contrôleurs

## 4. Plan d'Implémentation des Corrections

### Phase 1 : Correction des Permissions Utilisateur
- [ ] Mettre à jour le contrôleur UserController pour filtrer les utilisateurs visibles par l'admin
- [ ] Modifier les vues pour masquer les boutons d'action non autorisés
- [ ] Ajouter des validations pour empêcher la modification des rôles par un admin

### Phase 2 : Gestion des Accès aux Files d'Attente
- [ ] Mettre à jour le contrôleur QueueController pour filtrer les files en fonction du rôle
- [ ] Implémenter les scopes pour filtrer les files accessibles
- [ ] Mettre à jour les middlewares pour gérer les accès aux files

### Phase 3 : Interface Utilisateur
- [ ] Mettre à jour les vues pour masquer les boutons non autorisés
- [ ] Ajouter des messages d'erreur explicites pour les accès refusés
- [ ] Améliorer le feedback utilisateur lors des actions non autorisées

### Phase 4 : Tests et Validation
- [ ] Tester chaque rôle avec différents scénarios
- [ ] Vérifier que les restrictions sont correctement appliquées
- [ ] Tester les cas limites et les erreurs potentielles

### Phase 4 : Documentation et Déploiement
- [ ] Mettre à jour la documentation technique
  - [ ] Documenter la nouvelle structure des rôles
  - [ ] Mettre à jour le guide des bonnes pratiques
- [ ] Préparer la documentation utilisateur
  - [ ] Guide d'attribution des rôles
  - [ ] Procédures de dépannage
- [ ] Planifier le déploiement en production
  - [ ] Préparer le script de migration
  - [ ] Planifier une fenêtre de maintenance
  - [ ] Préparer le plan de rollback

## 5. Documentation Technique

### 5.1 Structure des Permissions

#### Fichier: `app/Enums/UserRole.php`
```php
class UserRole: string {
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case AGENT = 'agent';
    
    // Méthodes pour gérer les permissions
    public function getPermissions(): array { ... }
}
```

#### Permissions disponibles:
- `users.view` - Voir la liste des utilisateurs
- `users.create` - Créer un nouvel utilisateur
- `users.edit` - Modifier un utilisateur
- `users.delete` - Supprimer un utilisateur
- `queues.view_all` - Voir toutes les files d'attente
- `queues.create` - Créer une nouvelle file d'attente
- `queues.edit` - Modifier une file d'attente
- `queues.delete` - Supprimer une file d'attente
- `settings.manage` - Gérer les paramètres système
- `settings.view` - Voir les paramètres système

### 5.2 Bonnes Pratiques

#### Dans les contrôleurs:
```php
// Vérifier une permission
if (!auth()->user()->can('users.create')) {
    abort(403, 'Action non autorisée.');
}

// Filtrer les données en fonction du rôle
if (auth()->user()->hasRole('agent')) {
    $queues = Queue::whereHas('users', function($q) {
        $q->where('user_id', auth()->id());
    })->get();
} else {
    $queues = Queue::all();
}
```

#### Dans les vues:
```blade
@can('queues.edit')
    <a href="{{ route('queues.edit', $queue) }}" class="btn btn-edit">
        Modifier
    </a>
@endcan
```

## 6. Prochaines Étapes

1. **Correction des permissions utilisateur**
   - Implémenter le filtrage des utilisateurs visibles par les admins
   - Empêcher la modification des rôles par un admin

2. **Gestion des accès aux files**
   - Mettre en place les scopes pour filtrer les files accessibles
   - Adapter les contrôleurs pour respecter les restrictions d'accès

3. **Amélioration de l'interface**
   - Masquer les boutons non autorisés
   - Améliorer les messages d'erreur

4. **Tests approfondis**
   - Tous les scénarios d'utilisation
   - Vérification des accès aux différentes fonctionnalités
   - Tests de sécurité
- Code plus propre et plus maintenable
- Meilleure séparation des préoccupations

## 7. Prochaines Étapes

### Tests à Effectuer
- [ ] Vérifier la connexion avec chaque rôle
- [ ] Tester les permissions sur les fichiers d'attente
- [ ] Vérifier les accès administrateur
- [ ] Tester les cas limites

### Documentation à Mettre à Jour
- [ ] Guide du développeur
- [ ] Manuel d'administration
- [ ] Journal des modifications

## 8. Suivi des Problèmes

| Problème | Statut | Priorité | Commentaire |
|----------|--------|-----------|-------------|
| Tests de connexion | En cours | Haute | En cours de validation |
| Documentation technique | À faire | Moyenne | À mettre à jour avec les dernières modifications |
| Tests d'intégration | En attente | Haute | À planifier |
| Formation administrateurs | Non commencé | Moyenne | Préparer la documentation |
