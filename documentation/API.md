# 📘 Documentation API - EauTrack Rural

## Base URL
```
http://localhost/eautrack-rural/api
```

## Endpoints disponibles

### 1. 👥 Profils Utilisateurs

#### GET `/profiles.php`
Récupérer tous les profils
```bash
curl "http://localhost/eautrack-rural/api/profiles.php"
```

#### GET `/profiles.php?id={id}`
Récupérer un profil spécifique
```bash
curl "http://localhost/eautrack-rural/api/profiles.php?id=1"
```

#### POST `/profiles.php`
Créer un nouveau profil
```bash
curl -X POST http://localhost/eautrack-rural/api/profiles.php \
  -H "Content-Type: application/json" \
  -d '{
    "nom": "Famille Alami",
    "nb_personnes": 4,
    "type_habitation": "maison",
    "quota_jour": 150,
    "village": "Nador"
  }'
```

**Réponse:**
```json
{
  "success": true,
  "message": "Profil créé avec succès",
  "id": 1
}
```

---

### 2. 💧 Consommations

#### POST `/consumptions.php`
Enregistrer une nouvelle consommation
```bash
curl -X POST http://localhost/eautrack-rural/api/consumptions.php \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "activity_id": 1,
    "volume": 50
  }'
```

**Réponse:**
```json
{
  "success": true,
  "consumption_id": 5,
  "message": "Consommation enregistrée avec succès"
}
```

#### GET `/consumptions.php?user_id={id}`
Récupérer les données de consommation d'un utilisateur
```bash
curl "http://localhost/eautrack-rural/api/consumptions.php?user_id=1"
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "daily_total": 125.5,
    "breakdown": [
      {
        "activity": "Douche",
        "total_volume": "80",
        "count": "2"
      }
    ],
    "alerts": [...]
  }
}
```

---

### 3. 📊 Statistiques

#### GET `/stats.php?user_id={id}`
Récupérer les statistiques complètes
```bash
curl "http://localhost/eautrack-rural/api/stats.php?user_id=1"
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "daily_total": 125.5,
    "quota_total": 600,
    "percentage": 20.9,
    "waste_score": 20,
    "badges": [],
    "badge_count": 0,
    "breakdown": [...]
  }
}
```

---

### 4. 🔄 Synchronisation Offline

#### POST `/sync.php`
Synchroniser plusieurs consommations offline
```bash
curl -X POST http://localhost/eautrack-rural/api/sync.php \
  -H "Content-Type: application/json" \
  -d '{
    "consumptions": [
      {"user_id": 1, "activity_id": 1, "volume": 30},
      {"user_id": 1, "activity_id": 2, "volume": 15}
    ]
  }'
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "synced_count": 2,
    "error_count": 0,
    "synced_ids": [12, 13],
    "errors": []
  }
}
```

---

## 🎯 Activités disponibles

| ID | Activité | Volume Éco | Volume Max | Catégorie |
|----|----------|-----------|-----------|-----------|
| 1 | Douche | 50L | 100L | domestique |
| 2 | Vaisselle | 15L | 30L | domestique |
| 3 | Lessive | 40L | 80L | domestique |
| 4 | WC (chasse) | 6L | 12L | domestique |
| 5 | Lavage mains | 3L | 5L | domestique |
| 6 | Arrosage jardin | 20L | 50L | domestique |
| 7 | Irrigation champ | 200L | 500L | agricole |
| 8 | Abreuvoir bétail | 100L | 300L | agricole |
| 9 | Nettoyage étable | 50L | 150L | agricole |
| 10 | Fontaine publique | 10L | 20L | collectif |

---

## ⚠️ Système d'alertes

### Seuils
- **50%** du quota → Alerte INFO (niveau 1)
- **80%** du quota → Alerte WARNING (niveau 2)
- **100%** du quota → Alerte CRITICAL (niveau 3)

### Format des alertes
```json
{
  "id": 1,
  "user_id": 1,
  "consumption_id": 5,
  "level": 2,
  "message": "⚡ ATTENTION : Vous avez atteint 80% de votre quota",
  "created_at": "2025-01-08 14:30:00"
}
```

---

## 🏆 Système de Badges

### Badges disponibles
- **🌊 Eco Warrior**: 7 jours consécutifs sous le quota
- **💧 Water Saver**: Réduction ≥20% par rapport à la semaine précédente
- **🏅 Week Champion**: Meilleure semaine
- **⭐ Month Hero**: Meilleur mois

---

## 🔒 Codes d'erreur

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide |
| 404 | Ressource non trouvée |
| 405 | Méthode non autorisée |
| 500 | Erreur serveur |

---

## 💡 Exemples d'utilisation

### Scénario complet: Enregistrer une douche
```bash
# 1. Créer un profil
curl -X POST http://localhost/eautrack-rural/api/profiles.php \
  -H "Content-Type: application/json" \
  -d '{"nom":"Hassan","nb_personnes":3,"type_habitation":"maison","quota_jour":150,"village":"Nador"}'

# 2. Enregistrer une douche (50L)
curl -X POST http://localhost/eautrack-rural/api/consumptions.php \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"activity_id":1,"volume":50}'

# 3. Vérifier les stats
curl "http://localhost/eautrack-rural/api/stats.php?user_id=1"
```