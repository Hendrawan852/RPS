<?php
require_once '../config/database.php';
session_start();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT prodi_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$prodi_id = $stmt->fetchColumn();

echo "User Prodi ID: $prodi_id\n";

$stmt = $pdo->prepare("SELECT id, kode_mk, nama_mk, prodi_id, dosen_id FROM mata_kuliah WHERE prodi_id = ? OR prodi_id IS NULL");
$stmt->execute([$prodi_id]);
$mks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Courses reachable by Kaprodi:\n";
print_r($mks);
?>
