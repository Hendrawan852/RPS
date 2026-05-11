<?php
require_once '../config/database.php';

echo "=== FIXING NULL prodi_id in mata_kuliah ===\n";

// Assign all orphan courses (prodi_id IS NULL) to prodi 1
$stmt = $pdo->prepare("UPDATE mata_kuliah SET prodi_id = 1 WHERE prodi_id IS NULL");
$stmt->execute();
echo "Fixed " . $stmt->rowCount() . " orphaned courses.\n\n";

echo "=== FINAL STATE ===\n";
$rows = $pdo->query("SELECT id, kode_mk, nama_mk, prodi_id, dosen_id FROM mata_kuliah")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

echo "\n=== TEST: Save assignment for mata_kuliah id=1 to dosen id=1 ===\n";
$stmt = $pdo->prepare("UPDATE mata_kuliah SET dosen_id = ?, prodi_id = ? WHERE id = ?");
$result = $stmt->execute([1, 1, 1]);
echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
echo "Rows affected: " . $stmt->rowCount() . "\n";

$check = $pdo->query("SELECT id, kode_mk, dosen_id, prodi_id FROM mata_kuliah WHERE id=1")->fetch(PDO::FETCH_ASSOC);
echo "After update: "; print_r($check);
?>
