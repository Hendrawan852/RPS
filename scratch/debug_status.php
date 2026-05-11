<?php
require_once '../config/database.php';

echo "Checking pengajuan_rps status...\n";
$stmt = $pdo->query("SELECT p.id, p.status, m.nama_mk, m.prodi_id 
                    FROM pengajuan_rps p 
                    JOIN mata_kuliah m ON p.mk_id = m.id 
                    LIMIT 5");
while ($row = $stmt->fetch()) {
    print_r($row);
}

echo "\nChecking prodi_id for first Kaprodi user...\n";
$stmt = $pdo->query("SELECT id, username, role, prodi_id FROM users WHERE role = 'Kaprodi' LIMIT 1");
print_r($stmt->fetch());
?>
