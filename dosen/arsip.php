<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Arsip & Referensi RPS</h2>
    <p>Gunakan RPS lama yang sudah disetujui sebagai referensi atau template resmi.</p>
</div>

<div class="archive-grid">
    <div class="card">
        <div class="card-title">RPS Approved Sebelumnya</div>
        <ul class="archive-list">
            <li>
                <div class="archive-info">
                    <strong>RPS Basis Data v2.0</strong>
                    <span>Semester Gasal 2022/2023</span>
                </div>
                <button class="btn btn-outline btn-sm"><i class="fas fa-file-download"></i></button>
            </li>
            <li>
                <div class="archive-info">
                    <strong>RPS Pemrograman Dasar v1.1</strong>
                    <span>Semester Gasal 2021/2022</span>
                </div>
                <button class="btn btn-outline btn-sm"><i class="fas fa-file-download"></i></button>
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-title">Template & Panduan</div>
        <div class="template-item">
            <i class="fas fa-file-word fa-2x text-primary"></i>
            <div class="template-text">
                <strong>Template RPS SN-Dikti</strong>
                <p>Format standar RPS prodi Informatika.</p>
            </div>
            <button class="btn btn-link"><i class="fas fa-download"></i></button>
        </div>
        <div class="template-item">
            <i class="fas fa-file-pdf fa-2x text-danger"></i>
            <div class="template-text">
                <strong>Panduan Penyusunan RPS</strong>
                <p>Panduan pengisian deskripsi CPL/CPMK.</p>
            </div>
            <button class="btn btn-link"><i class="fas fa-download"></i></button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
