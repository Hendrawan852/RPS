<?php
require 'config/database.php';
$stmt = $pdo->prepare('SELECT id, nama_lengkap, prodi_id FROM users WHERE id = ?');
$stmt->execute([10]);
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
