<?php
$pdo = new PDO('mysql:host=localhost;dbname=rps_db', 'root', '');
$stmt = $pdo->prepare('SELECT id, nama_lengkap, prodi_id FROM users WHERE role = "Kaprodi"');
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->prepare('SELECT DISTINCT prodi_id FROM mata_kuliah');
$stmt->execute();
echo "Prodi IDs in mata_kuliah:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->prepare('SELECT p.id, m.nama_mk, m.prodi_id FROM pengajuan_rps p JOIN mata_kuliah m ON p.mk_id = m.id');
$stmt->execute();
echo "Submissions and their MK prodi_id:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
