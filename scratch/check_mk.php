<?php
require_once 'c:/laragon/www/RPS/config/database.php';
$stmt = $pdo->query("DESCRIBE mata_kuliah");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
