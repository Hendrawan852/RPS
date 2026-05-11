<?php
require_once 'config/database.php';
try {
    $pdo->exec("ALTER TABLE mk_cpmk ADD COLUMN tugas1 INT DEFAULT 0");
    $pdo->exec("ALTER TABLE mk_cpmk ADD COLUMN tugas2 INT DEFAULT 0");
    $pdo->exec("ALTER TABLE mk_cpmk ADD COLUMN tugas3 INT DEFAULT 0");
    $pdo->exec("ALTER TABLE mk_cpmk ADD COLUMN proyek1 INT DEFAULT 0");
    $pdo->exec("ALTER TABLE mk_cpmk ADD COLUMN proyek2 INT DEFAULT 0");
    echo "Columns added successfully to mk_cpmk table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
