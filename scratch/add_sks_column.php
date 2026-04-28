<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo->exec("ALTER TABLE pengajuan_rps ADD COLUMN sks INT AFTER semester");
    echo "Column 'sks' added successfully to 'pengajuan_rps' table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
