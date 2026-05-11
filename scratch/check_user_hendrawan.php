<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query("SELECT id, nama_lengkap, nip_nidn FROM users WHERE nama_lengkap LIKE '%Hendrawan%'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
