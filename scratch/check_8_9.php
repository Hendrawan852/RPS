<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT id, nama_mk, prodi_id FROM mata_kuliah WHERE id IN (8, 9)');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
