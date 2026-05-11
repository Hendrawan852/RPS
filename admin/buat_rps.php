<?php
require_once '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle form submission if needed (Logic can be added here later)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_rps'])) {
    // Basic success message for demonstration
    $_SESSION['msg'] = "RPS Baru berhasil disimpan secara lokal.";
}
?>

<div class="page-header">
    <h2>Buat RPS</h2>
    <p>Susun Rencana Pembelajaran Semester (RPS) baru untuk mata kuliah.</p>
</div>

<div class="content-body">
    <?php include 'includes/rps.php'; ?>
</div>

<?php include 'includes/footer.php'; ?>
