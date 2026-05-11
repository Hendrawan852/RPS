<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$count = $pdo->exec('UPDATE users SET prodi_id = 1 WHERE id = 28');
echo "Updated $count user(s).\n";
