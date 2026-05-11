<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT * FROM sub_cpmk');
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total Rows: " . count($data) . "\n";
print_r($data);
?>
