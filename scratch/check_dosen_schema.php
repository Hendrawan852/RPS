<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query("DESCRIBE dosen");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
