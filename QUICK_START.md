# SmartQueue - Guide de Démarrage Rapide

## 🚀 Installation Express (5 minutes)

### 1. Prérequis
```bash
# Vérifier les versions
php --version  # PHP 8.2+
composer --version
node --version  # Node.js 18+
```

### 2. Installation
```bash
# Cloner et installer
git clone [repository-url] SmartQueue
cd SmartQueue
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

### 3. Démarrer le serveur
```bash
php artisan serve
# Ouvrir http://localhost:8000
```

## 👤 Premier Utilisateur

### Créer un Super-Admin
```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin Principal';
$user->email = 'admin@example.com';
$user->password = bcrypt('password123');
$user->save();

$superAdminRole = App\Models\Role::where('slug', 'super-admin')->first();
$user->roles()->attach($superAdminRole->id);
```

### Se connecter
- URL : http://localhost:8000/login
- Email : admin@example.com
- Mot de passe : password123

## 🎯 Premiers Pas

### 1. Créer un Établissement
- Aller dans `/admin/settings/establishment`
- Configurer le nom et le type d'établissement
- Sauvegarder

### 2. Créer une File d'Attente
- Aller dans `/admin/queues/create`
- Nom : "Guichet Principal"
- Code : "GUICHET1"
- Activer la file

### 3. Créer un Agent
- Aller dans `/admin/users/create`
- Nom : "Agent Test"
- Email : "agent@example.com"
- Rôle : "agent"
- Sauvegarder

### 4. Attribuer l'Agent à la File
- Aller dans `/admin/queues/1/permissions`
- Ajouter l'agent avec permission "manage"

## 🧪 Tests Rapides

### Test de l'Interface Publique
```bash
# Ouvrir dans le navigateur
http://localhost:8000/
```

### Test de l'Interface Agent
```bash
# Se connecter avec l'agent
http://localhost:8000/agent/queues/1
```

### Test des Permissions
```bash
# Exécuter le script de test
php test_admin_interface.php
```

## 📁 Structure des Fichiers

```
SmartQueue/
├── app/
│   ├── Http/Controllers/Admin/     # Contrôleurs admin
│   ├── Models/                     # Modèles Eloquent
│   ├── Traits/                     # Traits (HasPermissions)
│   └── Http/Middleware/            # Middlewares
├── resources/views/admin/          # Vues admin
├── database/migrations/            # Migrations
├── routes/web.php                  # Routes
└── tests/                          # Tests
```

## 🔧 Configuration Importante

### Variables d'Environnement
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartqueue
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Permissions des Dossiers
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

## 🐛 Dépannage

### Erreurs Courantes

#### "Class not found"
```bash
composer dump-autoload
```

#### "Migration failed"
```bash
php artisan migrate:fresh --seed
```

#### "View not found"
```bash
php artisan view:clear
php artisan cache:clear
```

#### "Permission denied"
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Logs
```bash
# Voir les logs
tail -f storage/logs/laravel.log

# Vider les logs
php artisan log:clear
```

## 📚 Ressources

### Documentation
- [Documentation complète](DOCUMENTATION.md)
- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)

### Outils de Développement
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Laravel Telescope](https://laravel.com/docs/telescope)

## 🚀 Prochaines Étapes

### Développement
1. Créer des tests unitaires
2. Ajouter des validations
3. Implémenter les notifications
4. Optimiser les performances

### Production
1. Configurer l'environnement de production
2. Mettre en place le monitoring
3. Configurer les sauvegardes
4. Déployer l'application

---

**Besoin d'aide ?** Consultez la [documentation complète](DOCUMENTATION.md) ou créez une issue sur GitHub. 