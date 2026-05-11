<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->query('SHOW CREATE TABLE pengajuan_rps');
echo $stmt->fetchColumn(1);
