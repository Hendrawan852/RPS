<?php
require_once 'config/database.php';
echo "USERS Table:\n";
$users = $pdo->query("SELECT id, username, prodi_id, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
print_r($users);

echo "\nMATA KULIAH Table (Sample):\n";
$mk = $pdo->query("SELECT id, nama_mk, prodi_id FROM mata_kuliah LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($mk);

echo "\nPRODI Table:\n";
$prodi = $pdo->query("SELECT id, nama_prodi FROM prodi")->fetchAll(PDO::FETCH_ASSOC);
print_r($prodi);
