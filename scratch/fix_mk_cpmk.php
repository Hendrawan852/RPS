<?php
require_once '../config/database.php';

try {
    echo "Adding assessment columns to 'mk_cpmk' table...\n";
    $pdo->exec("ALTER TABLE mk_cpmk 
                ADD COLUMN tugas1 INT DEFAULT 0,
                ADD COLUMN tugas2 INT DEFAULT 0,
                ADD COLUMN tugas3 INT DEFAULT 0,
                ADD COLUMN proyek1 INT DEFAULT 0,
                ADD COLUMN proyek2 INT DEFAULT 0");
    echo "Columns added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
