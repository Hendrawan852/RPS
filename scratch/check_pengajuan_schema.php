<?php
require 'config/database.php';
$stmt = $pdo->query('DESCRIBE pengajuan_rps');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
