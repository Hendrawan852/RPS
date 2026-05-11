<?php
require 'config/database.php';
$stmt = $pdo->query('DESCRIBE sub_cpmk');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
