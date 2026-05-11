<?php
require_once '../config/database.php';

echo "Checking columns in mk_cpmk table...\n";
$stmt = $pdo->query("DESCRIBE mk_cpmk");
while ($row = $stmt->fetch()) {
    print_r($row);
}
?>
