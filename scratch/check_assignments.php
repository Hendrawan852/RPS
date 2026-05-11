<?php
require 'config/database.php';
$stmt = $pdo->query('SELECT mk.id, mk.nama_mk, mk.dosen_id, u.nama_lengkap FROM mata_kuliah mk LEFT JOIN users u ON mk.dosen_id = u.id');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
