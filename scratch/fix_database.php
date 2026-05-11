<?php
require_once '../config/database.php';

try {
    echo "Adding column 'catatan_revisi' to 'pengajuan_rps' table...\n";
    $pdo->exec("ALTER TABLE pengajuan_rps ADD COLUMN catatan_revisi TEXT NULL AFTER status");
    echo "Column added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column already exists.\n";
    }
}
?>
