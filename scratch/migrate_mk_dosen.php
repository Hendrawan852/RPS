<?php
require 'config/database.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM mata_kuliah LIKE 'dosen_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE mata_kuliah ADD COLUMN dosen_id INT NULL AFTER prodi_id");
        echo "Column dosen_id added to mata_kuliah table.\n";
    } else {
        echo "Column dosen_id already exists in mata_kuliah table.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
