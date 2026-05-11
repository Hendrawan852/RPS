<?php
require_once '../config/database.php';
$stmt = $pdo->query("SELECT id, nama FROM dosen");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>
