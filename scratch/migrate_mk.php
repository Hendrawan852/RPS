<?php
require 'config/database.php';
try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM mata_kuliah LIKE 'prodi_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE mata_kuliah ADD COLUMN prodi_id INT NULL AFTER id");
        echo "Column prodi_id added to mata_kuliah table.\n";
    } else {
        echo "Column prodi_id already exists in mata_kuliah table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
