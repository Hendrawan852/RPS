<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query('SELECT * FROM prodi');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
