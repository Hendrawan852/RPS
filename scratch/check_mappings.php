<?php
require 'config/database.php';
echo "mk_cpl:\n";
$stmt = $pdo->query('DESCRIBE mk_cpl');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\nmk_cpmk:\n";
$stmt = $pdo->query('DESCRIBE mk_cpmk');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
