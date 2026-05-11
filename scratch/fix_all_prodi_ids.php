<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$count = $pdo->exec('UPDATE users SET prodi_id = 1 WHERE role IN ("Dosen", "Kaprodi") AND (prodi_id IS NULL OR prodi_id = 0)');
echo "Updated $count user(s).\n";
