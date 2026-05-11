<?php
require 'config/database.php';
try {
    $queries = [
        "CREATE TABLE IF NOT EXISTS cpl (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            kode_cpl VARCHAR(20) NOT NULL UNIQUE, 
            deskripsi TEXT NOT NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS cpmk (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            kode_cpmk VARCHAR(20) NOT NULL UNIQUE, 
            deskripsi TEXT NOT NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS mk_cpl (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            mk_id INT NOT NULL, 
            cpl_id INT NOT NULL,
            UNIQUE KEY (mk_id, cpl_id)
        )",
        "CREATE TABLE IF NOT EXISTS mk_cpmk (
            id INT AUTO_INCREMENT PRIMARY KEY, 
            mk_id INT NOT NULL, 
            cpmk_id INT NOT NULL,
            UNIQUE KEY (mk_id, cpmk_id)
        )"
    ];
    
    foreach ($queries as $q) {
        $pdo->exec($q);
        echo "Executed query successfully.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
