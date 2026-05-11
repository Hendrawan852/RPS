<?php
require_once 'c:/laragon/www/RPS/config/database.php';
$stmt = $pdo->query("SHOW TABLES");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
