<?php
require_once 'config/database.php';
try {
    // Reset dosen_id mappings that don't exist in the new dosen table
    $stmt = $pdo->exec("UPDATE mata_kuliah SET dosen_id = NULL WHERE dosen_id IS NOT NULL AND dosen_id NOT IN (SELECT id FROM dosen)");
    echo "Successfully reset $stmt invalid lecturer mappings.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
