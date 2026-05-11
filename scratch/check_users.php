<?php
require_once '../config/database.php';
$stmt = $pdo->query("SELECT id, email, role, prodi_id FROM users");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>
