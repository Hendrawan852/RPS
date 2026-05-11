<?php
/**
 * Test ajax_data_master.php directly via simulated POST
 */
require_once '../config/database.php';

echo "=== BEFORE SAVE ===\n";
$row = $pdo->query("SELECT id, kode_mk, dosen_id, prodi_id FROM mata_kuliah WHERE id = 7")->fetch(PDO::FETCH_ASSOC);
print_r($row);

// Simulate what ajax_data_master.php does for update_dosen_mk
$prodi_id = 1; // Kaprodi's prodi_id
$assignments = [7 => 1]; // TIF0909 -> Dr. Hendrawan (dosen.id=1)

foreach ($assignments as $mk_id => $dosen_id) {
    $d_id = ($dosen_id !== '' && $dosen_id !== null) ? (int)$dosen_id : null;
    $mk_id = (int)$mk_id;
    echo "\nRunning: UPDATE mata_kuliah SET dosen_id=$d_id, prodi_id=$prodi_id WHERE id=$mk_id\n";
    $stmt = $pdo->prepare("UPDATE mata_kuliah SET dosen_id = ?, prodi_id = ? WHERE id = ?");
    $result = $stmt->execute([$d_id, $prodi_id, $mk_id]);
    echo "Result: " . ($result ? 'SUCCESS' : 'FAILED') . " | Rows affected: " . $stmt->rowCount() . "\n";
}

echo "\n=== AFTER SAVE ===\n";
$row = $pdo->query("SELECT id, kode_mk, dosen_id, prodi_id FROM mata_kuliah WHERE id = 7")->fetch(PDO::FETCH_ASSOC);
print_r($row);
?>
