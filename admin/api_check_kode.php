<?php
require_once '../config/database.php';

$kode = $_GET['kode'] ?? '';
$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'mk'; // mk, cpl, or cpmk

if (empty($kode)) {
    echo json_encode(['available' => true]);
    exit;
}

$column = 'kode_mk';
$table = 'mata_kuliah';

if ($type === 'cpl') {
    $column = 'kode_cpl';
    $table = 'cpl';
} elseif ($type === 'cpmk') {
    $column = 'kode_cpmk';
    $table = 'cpmk';
}

$stmt = $pdo->prepare("SELECT id FROM $table WHERE $column = ? AND id != ?");
$stmt->execute([$kode, $id]);
$exists = $stmt->fetch();

echo json_encode(['available' => !$exists]);
?>
