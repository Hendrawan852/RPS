<?php
require 'config/database.php';
try {
    $pdo->exec("ALTER TABLE mata_kuliah ADD COLUMN deskripsi TEXT AFTER semester_default");
    echo "Column 'deskripsi' added successfully to 'mata_kuliah'.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
