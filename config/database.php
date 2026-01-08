<?php
// config/database.php
return [
    'host' => '127.0.0.1',  // Utilise l'IP au lieu de 'localhost'
    'port' => '3306',        // Ajoute le port explicitement
    'dbname' => 'eautrack_rural',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];