# Système de Permissions SmartQueue

## Vue d'ensemble

Le système de permissions de SmartQueue est un système granulaire qui permet de contrôler l'accès des utilisateurs aux différentes fonctionnalités de l'application. Il combine des rôles globaux avec des permissions spécifiques aux files d'attente.

## Architecture

### 1. Rôles Globaux

Les rôles définissent des ensembles de permissions prédéfinis :

- **super-admin** : Accès complet à toutes les fonctionnalités
- **admin** : Gestion des utilisateurs, rôles et paramètres généraux
- **agent-manager** : Gestion des files d'attente et attribution de permissions
- **agent** : Accès limité aux files d'attente assignées

### 2. Permissions Granulaires

Chaque utilisateur peut avoir des permissions spécifiques sur des files d'attente individuelles :

- **view** : Voir la file d'attente et ses tickets
- **manage_tickets** : Gérer les tickets (créer, modifier, supprimer)
- **manage** : Gestion complète de la file d'attente (inclut toutes les permissions)

## Structure de la Base de Données

### Tables Principales

1. **permissions** : Définit les permissions disponibles
2. **roles** : Définit les rôles avec leurs descriptions
3. **role_permissions** : Table pivot liant les rôles aux permissions
4. **user_roles** : Table pivot liant les utilisateurs aux rôles
5. **queue_permissions** : Permissions spécifiques aux files d'attente

### Relations

```
User -> user_roles -> Role -> role_permissions -> Permission
User -> queue_permissions -> Queue
```

## Utilisation

### Vérification des Permissions

```php
// Vérifier une permission globale
if ($user->can('manage_users')) {
    // L'utilisateur peut gérer les utilisateurs
}

// Vérifier une permission sur un modèle spécifique
if ($user->can('view', $queue)) {
    // L'utilisateur peut voir cette file d'attente
}

// Vérifier un rôle
if ($user->hasRole('admin')) {
    // L'utilisateur a le rôle admin
}
```

### Attribution de Permissions

```php
// Attribuer un rôle
$user->roles()->attach($roleId);

// Attribuer une permission sur une file d'attente
$user->grantQueuePermission($queueId, 'manage');

// Révoquer une permission
$user->revokeQueuePermission($queueId, 'view');
```

### Filtrage des Données

```php
// Obtenir les IDs des files d'attente accessibles
$accessibleQueueIds = $user->getAccessibleQueueIds();

// Filtrer les files d'attente
$queues = Queue::whereIn('id', $accessibleQueueIds)->get();
```

## Hiérarchie des Permissions

### Permissions de Files d'Attente

1. **manage** (niveau le plus élevé)
   - Inclut : view, update, delete, manage_tickets
   - Permet la gestion complète de la file d'attente

2. **manage_tickets**
   - Inclut : view
   - Permet de gérer les tickets (créer, modifier, supprimer)

3. **view** (niveau le plus bas)
   - Permet seulement de voir la file d'attente et ses tickets

### Permissions Globales

- **manage_users** : Gestion des utilisateurs
- **manage_roles** : Gestion des rôles
- **manage_settings** : Gestion des paramètres
- **view_queues** : Voir toutes les files d'attente
- **create_queues** : Créer des files d'attente

## Interface d'Administration

### Gestion des Utilisateurs

- **Route** : `/admin/users`
- **Fonctionnalités** :
  - Liste des utilisateurs
  - Création d'utilisateurs
  - Modification d'utilisateurs
  - Attribution de rôles

### Gestion des Rôles

- **Route** : `/admin/roles`
- **Fonctionnalités** :
  - Liste des rôles
  - Création de rôles
  - Modification de rôles
  - Attribution de permissions

### Gestion des Permissions de Files d'Attente

- **Route** : `/admin/queues/{queue}/permissions`
- **Fonctionnalités** :
  - Attribution de permissions à des utilisateurs
  - Révocation de permissions
  - Vue d'ensemble des permissions actuelles

## Middleware

### CheckRole

Vérifie que l'utilisateur a au moins un des rôles spécifiés :

```php
Route::middleware(['auth', 'role:admin,super-admin'])->group(function () {
    // Routes accessibles aux admins et super-admins
});
```

### AdminMiddleware

Vérifie que l'utilisateur a des permissions d'administration :

```php
Route::middleware(['auth', 'admin'])->group(function () {
    // Routes d'administration
});
```

## Tests

Le système inclut des tests complets dans `tests/Feature/PermissionSystemTest.php` qui vérifient :

- Attribution et vérification de rôles
- Permissions spécifiques aux files d'attente
- Hiérarchie des permissions
- Filtrage des données selon les permissions

## Bonnes Pratiques

1. **Toujours vérifier les permissions** avant d'accéder aux données
2. **Utiliser le filtrage automatique** pour les listes de files d'attente
3. **Documenter les nouvelles permissions** ajoutées
4. **Tester les permissions** lors de l'ajout de nouvelles fonctionnalités
5. **Utiliser les rôles prédéfinis** plutôt que de créer des permissions personnalisées

## Extension du Système

### Ajouter une Nouvelle Permission

1. Ajouter la permission dans le seeder
2. Mettre à jour la documentation
3. Ajouter les vérifications dans les contrôleurs
4. Créer des tests

### Ajouter un Nouveau Rôle

1. Créer le rôle dans le seeder
2. Définir ses permissions
3. Mettre à jour la documentation
4. Ajouter des tests

## Sécurité

- Les permissions sont vérifiées côté serveur
- Les utilisateurs ne peuvent voir que les données auxquelles ils ont accès
- Les actions sont bloquées si l'utilisateur n'a pas les permissions nécessaires
- Les logs d'audit peuvent être ajoutés pour tracer les actions sensibles 
