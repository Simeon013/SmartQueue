# SmartQueue - Documentation Complète

## 📋 Vue d'ensemble

SmartQueue est une application Laravel 12 moderne pour la gestion de files d'attente dans des établissements. Elle offre un système complet de gestion des utilisateurs, rôles, permissions et files d'attente avec une interface d'administration intuitive.

## 🚀 Fonctionnalités Principales

### 👥 Gestion des Utilisateurs et Rôles
- **4 rôles prédéfinis** : super-admin, admin, agent-manager, agent
- **Permissions granulaires** sur les files d'attente
- **Interface d'administration** complète pour la gestion des utilisateurs
- **Système de permissions** flexible et extensible

### 🏢 Gestion des Établissements
- Configuration des établissements
- Types d'établissements personnalisables
- Paramètres spécifiques par établissement

### 📊 Gestion des Files d'Attente
- Création et gestion de files d'attente
- Codes uniques pour chaque file
- Statuts actif/inactif
- Permissions granulaires par utilisateur

### 🎫 Gestion des Tickets
- Génération automatique de tickets
- Statuts multiples (en attente, en cours, terminé, annulé)
- Historique complet des tickets
- Interface temps réel

### ⚙️ Paramètres Système
- Configuration automatique de fermeture
- Paramètres globaux de l'application
- Personnalisation des comportements

## 🏗️ Architecture Technique

### Base de Données
```
users                    # Utilisateurs
roles                    # Rôles
permissions              # Permissions
user_roles              # Relation utilisateur-rôles
role_permissions        # Relation rôle-permissions
establishments          # Établissements
queues                  # Files d'attente
tickets                 # Tickets
queue_permissions       # Permissions sur files d'attente
queue_events           # Événements de file d'attente
system_settings        # Paramètres système
```

### Modèles Principaux
- `User` : Gestion des utilisateurs avec trait `HasPermissions`
- `Role` : Définition des rôles
- `Permission` : Permissions système
- `Queue` : Files d'attente
- `Ticket` : Tickets de file d'attente
- `Establishment` : Établissements
- `QueuePermission` : Permissions granulaires sur les files

### Contrôleurs
- `UserController` : CRUD utilisateurs
- `RoleController` : CRUD rôles
- `QueueController` : Gestion des files d'attente
- `TicketController` : Gestion des tickets
- `EstablishmentController` : Gestion des établissements
- `SettingsController` : Paramètres système

### Middlewares
- `AdminMiddleware` : Vérification des droits admin
- `CheckRole` : Vérification des rôles

## 🔐 Système de Permissions

### Rôles Prédéfinis

#### Super-Admin
- Toutes les permissions
- Gestion complète du système
- Accès à tous les établissements

#### Admin
- Gestion des utilisateurs et rôles
- Gestion des files d'attente
- Gestion des paramètres
- Accès à toutes les files d'attente

#### Agent-Manager
- Gestion des files d'attente
- Gestion des tickets
- Attribution d'agents aux files
- Accès aux files assignées

#### Agent
- Gestion des tickets
- Accès aux files assignées
- Opérations de base

### Permissions Granulaires
- `view` : Voir une file d'attente
- `manage` : Gérer une file d'attente
- `manage_tickets` : Gérer les tickets d'une file
- `assign_agents` : Attribuer des agents à une file

## 🛠️ Installation et Configuration

### Prérequis
- PHP 8.2+
- Laravel 12
- MySQL/PostgreSQL
- Composer
- Node.js (pour les assets)

### Installation
```bash
# Cloner le projet
git clone [repository-url]
cd SmartQueue

# Installer les dépendances
composer install
npm install

# Configuration
cp .env.example .env
php artisan key:generate

# Base de données
php artisan migrate
php artisan db:seed

# Assets
npm run build
```

### Configuration
1. Configurer la base de données dans `.env`
2. Configurer les paramètres d'email
3. Configurer les paramètres de broadcasting (optionnel)
4. Créer un utilisateur super-admin

## 📱 Interface Utilisateur

### Interface Publique
- `/` : Liste des files d'attente publiques
- `/q/{code}` : Affichage d'une file d'attente
- `/q/{queue}/ticket/{ticket}` : Statut d'un ticket

### Interface Agent
- `/agent/queues/{queue}` : Dashboard agent
- Gestion des tickets en temps réel
- Appel des tickets suivants

### Interface Admin
- `/admin/dashboard` : Dashboard principal
- `/admin/users` : Gestion des utilisateurs
- `/admin/roles` : Gestion des rôles
- `/admin/queues` : Gestion des files d'attente
- `/admin/settings` : Paramètres système

## 🔧 API et Intégrations

### Routes API (à développer)
- Authentification JWT
- Endpoints REST pour les files d'attente
- Webhooks pour les événements
- API pour applications mobiles

### Intégrations Possibles
- Systèmes de paiement
- SMS/Email notifications
- Applications mobiles
- Tableaux d'affichage

## 🧪 Tests

### Tests Automatisés
```bash
# Tests unitaires
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Tests de permissions
php artisan test --filter=PermissionTest
```

### Scripts de Test
- `test_admin_interface.php` : Test complet de l'interface admin
- `test_web_interface.php` : Test de l'interface web
- Tests de permissions granulaires

## 📊 Monitoring et Logs

### Logs d'Audit
- Actions utilisateur
- Modifications de permissions
- Création/suppression d'éléments
- Connexions/déconnexions

### Métriques
- Nombre de tickets par file
- Temps d'attente moyen
- Utilisation des files
- Performance système

## 🚀 Déploiement

### Environnement de Production
1. Configuration serveur web (Apache/Nginx)
2. Configuration SSL
3. Optimisation des performances
4. Sauvegarde automatique
5. Monitoring

### Variables d'Environnement
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=redis
BROADCAST_DRIVER=pusher
```

## 🔮 Évolutions Futures

### Phase 2 - Fonctionnalités Avancées
- [ ] Support multi-établissements
- [ ] API REST complète
- [ ] Notifications push
- [ ] Tableaux d'affichage
- [ ] Applications mobiles

### Phase 3 - Intelligence Artificielle
- [ ] Prédiction des temps d'attente
- [ ] Optimisation automatique des files
- [ ] Analyse des tendances
- [ ] Recommandations intelligentes

### Phase 4 - Écosystème
- [ ] Marketplace d'extensions
- [ ] Intégrations tierces
- [ ] White-label solution
- [ ] SaaS multi-tenant

## 📞 Support et Maintenance

### Documentation
- Guide utilisateur
- Guide administrateur
- Documentation API
- FAQ

### Support
- Documentation en ligne
- Base de connaissances
- Support technique
- Formation utilisateurs

## 📄 Licence

Ce projet est sous licence [LICENCE]. Voir le fichier LICENSE pour plus de détails.

---

**SmartQueue** - Solution moderne de gestion de files d'attente
*Développé avec Laravel 12 et les meilleures pratiques* 