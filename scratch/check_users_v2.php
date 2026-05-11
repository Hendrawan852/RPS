<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query("SELECT id, nama_lengkap, role FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
