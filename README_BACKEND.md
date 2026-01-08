# 🔧 Backend EauTrack Rural - Guide Technique

## 🏗️ Architecture
```
eautrack-rural/
├── config/
│   ├── constants.php      # Constantes globales (seuils, badges)
│   └── database.php       # Configuration DB
├── core/
│   ├── Database.php       # Singleton de connexion
│   └── Model.php          # Classe de base pour les modèles
├── models/
│   ├── UserProfile.php    # Gestion des profils
│   ├── Consumption.php    # Gestion des consommations
│   ├── Alert.php          # Gestion des alertes
│   ├── Badge.php          # Gestion des badges
│   └── ActivityReference.php # Activités de référence
├── services/
│   ├── TrackerService.php # Service principal
│   ├── AlertService.php   # Logique des alertes
│   └── BadgeSystem.php    # Logique des badges
└── api/
    ├── index.php          # Router
    ├── profiles.php       # Endpoints profils
    ├── consumptions.php   # Endpoints consommations
    ├── stats.php          # Endpoints statistiques
    └── sync.php           # Synchronisation offline
```

## 🚀 Installation

### Prérequis
- PHP 8.0+
- MySQL 5.7+
- XAMPP/MAMP

### Étapes
```bash
# 1. Cloner le repo
git clone https://github.com/yaakoub0tair/eautrack-rural.git
cd eautrack-rural

# 2. Créer la base de données
mysql -u root < database/schema.sql
mysql -u root < database/seed.sql

# 3. Configurer la connexion
# Éditer config/database.php si nécessaire

# 4. Tester
php tests/test_backend.php
```

## 🧪 Tests

### Tests Backend (PHP)
```bash
php tests/test_backend.php
```

### Tests API (cURL)
```bash
./tests/test_api.sh
```

### Tests unitaires d'un endpoint
```bash
# Test création profil
curl -X POST http://localhost/eautrack-rural/api/profiles.php \
  -H "Content-Type: application/json" \
  -d '{"nom":"Test","nb_personnes":2,"type_habitation":"maison","quota_jour":150}'

# Test ajout consommation
curl -X POST http://localhost/eautrack-rural/api/consumptions.php \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"activity_id":1,"volume":30}'

# Test stats
curl "http://localhost/eautrack-rural/api/stats.php?user_id=1"
```

## 📊 Schéma de Base de Données

### Tables principales

#### `user_profiles`
- id, nom, nb_personnes, type_habitation, quota_jour, village

#### `activity_references`
- id, name, volume_eco, volume_max, category, alert_weight

#### `consumptions`
- id, user_id, activity_id, volume, date, time, synced

#### `alerts`
- id, user_id, consumption_id, level, message

#### `badges`
- id, user_id, badge_type, earned_at

## 🎯 Fonctionnalités

### ✅ Implémentées
- [x] CRUD profils utilisateurs
- [x] Enregistrement des consommations
- [x] Calcul automatique des quotas
- [x] Système d'alertes (50%, 80%, 100%)
- [x] Statistiques journalières
- [x] Répartition par activité
- [x] Système de badges
- [x] Synchronisation offline
- [x] API REST complète

### 🔜 À venir (optionnel)
- [ ] Graphiques hebdomadaires/mensuels
- [ ] Comparaison entre utilisateurs
- [ ] Export des données (CSV)
- [ ] Notifications push
- [ ] Prédictions de consommation

## 🛠️ Maintenance

### Vider les données de test
```sql
TRUNCATE TABLE consumptions;
TRUNCATE TABLE alerts;
TRUNCATE TABLE badges;
DELETE FROM user_profiles WHERE id > 3;
```

### Backup de la base
```bash
mysqldump -u root eautrack_rural > backup_$(date +%Y%m%d).sql
```

### Restaurer un backup
```bash
mysql -u root eautrack_rural < backup_20250108.sql
```

## 🐛 Debugging

### Activer les logs d'erreurs
Ajoute dans `api/index.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Vérifier les logs MySQL
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/mysql_error.log
```

## 📈 Performance

### Optimisations appliquées
- Index sur les foreign keys
- Requêtes préparées (PDO)
- Singleton pour la connexion DB
- Lazy loading des modèles

### Monitoring
```sql
-- Nombre de consommations aujourd'hui
SELECT COUNT(*) FROM consumptions WHERE date = CURDATE();

-- Utilisateurs les plus actifs
SELECT user_id, COUNT(*) as total 
FROM consumptions 
GROUP BY user_id 
ORDER BY total DESC 
LIMIT 5;
```

## 🔐 Sécurité

- ✅ Requêtes préparées (SQL injection)
- ✅ Validation des données
- ✅ CORS configuré
- ⚠️ À ajouter: Authentification JWT
- ⚠️ À ajouter: Rate limiting

## 📞 Support

Issues: https://github.com/yaakoub0tair/eautrack-rural/issues