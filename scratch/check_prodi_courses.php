<?php
require 'config/database.php';
$stmt = $pdo->prepare('SELECT id, nama_mk, prodi_id, dosen_id FROM mata_kuliah WHERE prodi_id = ?');
$stmt->execute([1]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
