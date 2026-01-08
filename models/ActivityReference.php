<?php
// models/ActivityReference.php
require_once __DIR__ . '/../core/Model.php';

class ActivityReference extends Model {
    protected string $table = 'activity_references';
    
    /**
     * Obtenir toutes les activités par catégorie
     */
    public function getByCategory(string $category): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE category = :category
            ORDER BY name ASC
        ");
        $stmt->execute(['category' => $category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les activités les plus fréquentes
     */
    public function getMostUsed(int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT ar.*, COUNT(c.id) as usage_count
            FROM {$this->table} ar
            LEFT JOIN consumptions c ON ar.id = c.activity_id
            GROUP BY ar.id
            ORDER BY usage_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}