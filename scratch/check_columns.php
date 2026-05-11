<?php
require_once '../config/database.php';

echo "Checking columns in pengajuan_rps table...\n";
$stmt = $pdo->query("DESCRIBE pengajuan_rps");
while ($row = $stmt->fetch()) {
    print_r($row);
}
?>
