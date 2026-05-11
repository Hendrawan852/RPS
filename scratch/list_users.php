<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT id, nama_lengkap, role FROM users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
