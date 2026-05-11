<?php
require 'config/database.php';
try {
    $queries = [
        "CREATE TABLE IF NOT EXISTS bahan_kajian (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            kode_bk VARCHAR(20) NOT NULL UNIQUE, 
            nama_bk VARCHAR(255) NOT NULL, 
            deskripsi TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS metode_pembelajaran (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            nama_metode VARCHAR(255) NOT NULL, 
            deskripsi TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($queries as $q) {
        $pdo->exec($q);
        echo "Executed query successfully.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
