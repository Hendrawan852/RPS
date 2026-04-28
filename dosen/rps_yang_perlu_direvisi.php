<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>RPS yang Perlu Direvisi</h2>
    <p>Segera perbaiki RPS Anda berdasarkan catatan dari Kaprodi untuk mendapatkan persetujuan.</p>
</div>

<div class="revision-container">
    <div class="card revision-card">
        <div class="revision-header">
            <div class="mk-info">
                <span class="badge badge-danger">Rejected</span>
                <h3>TIF201 - Struktur Data</h3>
                <p>Semester 3 - TA 2023/2024</p>
            </div>
            <a href="buat_rps_baru.php?id=1&action=edit" class="btn btn-primary"><i class="fas fa-edit"></i> Perbaiki Sekarang</a>
        </div>
        <div class="revision-note">
            <div class="note-title"><i class="fas fa-comment-dots"></i> Catatan Revisi Kaprodi:</div>
            <p>"Mohon sesuaikan kembali CPMK pada poin 2 dengan CPL-02. Referensi juga harap menggunakan versi terbaru (maksimal 10 tahun terakhir)."</p>
            <small>Dikirim oleh: <strong>Kaprodi Informatika</strong> - 2 hari yang lalu</small>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
