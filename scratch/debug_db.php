<?php
require_once 'config/database.php';
echo "--- DOSEN TABLE ---\n";
$stmt = $pdo->query("SELECT * FROM dosen");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
echo "\n--- MATA KULIAH TABLE ---\n";
$stmt = $pdo->query("SELECT id, kode_mk, nama_mk, dosen_id, prodi_id FROM mata_kuliah");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
