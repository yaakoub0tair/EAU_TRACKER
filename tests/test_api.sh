#!/bin/bash
# tests/test_api.sh

echo "🧪 Tests API EauTrack Rural"
echo "=========================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

BASE_URL="http://localhost/eautrack-rural/api"

# Test 1: Créer un profil
echo -e "${BLUE}Test 1: Création d'un profil${NC}"
response=$(curl -s -X POST "$BASE_URL/profiles.php" \
  -H "Content-Type: application/json" \
  -d '{"nom":"API Test","nb_personnes":4,"type_habitation":"maison","quota_jour":150,"village":"Nador"}')
echo "$response" | json_pp 2>/dev/null || echo "$response"
echo ""

# Test 2: Lister tous les profils
echo -e "${BLUE}Test 2: Liste des profils${NC}"
response=$(curl -s "$BASE_URL/profiles.php")
echo "$response" | json_pp 2>/dev/null || echo "$response"
echo ""

# Test 3: Ajouter une consommation
echo -e "${BLUE}Test 3: Ajouter une consommation${NC}"
response=$(curl -s -X POST "$BASE_URL/consumptions.php" \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"activity_id":1,"volume":50}')
echo "$response" | json_pp 2>/dev/null || echo "$response"
echo ""

# Test 4: Récupérer les stats
echo -e "${BLUE}Test 4: Statistiques utilisateur${NC}"
response=$(curl -s "$BASE_URL/stats.php?user_id=1")
echo "$response" | json_pp 2>/dev/null || echo "$response"
echo ""

# Test 5: Tester une consommation élevée (alerte)
echo -e "${BLUE}Test 5: Consommation élevée (test alertes)${NC}"
response=$(curl -s -X POST "$BASE_URL/consumptions.php" \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"activity_id":1,"volume":400}')
echo "$response" | json_pp 2>/dev/null || echo "$response"
echo ""

# Test 6: Vérifier les alertes
echo -e "${BLUE}Test 6: Récupérer les données avec alertes${NC}"
response=$(curl -s "$BASE_URL/consumptions.php?user_id=1")
echo "$response" | json_pp 2>/dev/null || echo "$response"
echo ""

echo -e "${GREEN}✨ Tests API terminés!${NC}"