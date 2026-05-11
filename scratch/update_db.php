<?php
require_once 'c:/laragon/www/RPS/config/database.php';
try {
    $pdo->exec("ALTER TABLE pengajuan_rps ADD COLUMN metode TEXT NULL, ADD COLUMN penilaian TEXT NULL, ADD COLUMN referensi TEXT NULL");
    echo "Columns added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
