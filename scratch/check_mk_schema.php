<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query('SHOW CREATE TABLE mata_kuliah');
echo $stmt->fetchColumn(1);
