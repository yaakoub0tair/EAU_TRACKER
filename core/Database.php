<?php
// core/Database.php
class Database {
    private static ?Database $instance = null;
    private PDO $connection;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        // Utilise l'IP et le port explicitement
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        
        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}