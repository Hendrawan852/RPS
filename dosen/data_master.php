<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Data Master Dosen</h2>
    <p>Referensi data akademik utama untuk penyusunan RPS Anda.</p>
</div>

<div class="card">
    <div class="card-title">Mata Kuliah yang Anda Ampu</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Semester</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>TIF205</td>
                    <td>Sistem Basis Data</td>
                    <td>3</td>
                    <td>Gasal</td>
                </tr>
                <tr>
                    <td>TIF401</td>
                    <td>Pemrograman Web</td>
                    <td>4</td>
                    <td>Genap</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-title">CPL & CPMK Terkait</div>
    <div class="info-list">
        <div class="info-item">
            <strong>CPL-01</strong>
            <p>Mampu menerapkan pemikiran logis, kritis, sistematis, dan inovatif dalam konteks pengembangan atau implementasi ilmu pengetahuan...</p>
        </div>
        <div class="info-item">
            <strong>CPMK-01</strong>
            <p>Mahasiswa mampu merancang skema database menggunakan ER Diagram sesuai dengan kebutuhan bisnis.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
