<?php
require_once 'c:/laragon/www/RPS/config/database.php';
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
echo "Users found: " . count($users) . "\n";
foreach ($users as $u) {
    echo "ID: {$u['id']}, NIP: {$u['nip_nidn']}, ProdiID: {$u['prodi_id']}, Role: {$u['role']}\n";
}
?>
