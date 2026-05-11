<?php
require_once '../config/database.php';

echo "=== CURRENT mata_kuliah STATE ===\n";
$stmt = $pdo->query("SELECT id, kode_mk, nama_mk, prodi_id, dosen_id FROM mata_kuliah");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

echo "\n=== DOSEN TABLE ===\n";
$stmt = $pdo->query("SELECT id, nama, prodi_id FROM dosen");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>
