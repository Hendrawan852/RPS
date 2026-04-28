<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Data Master (View Only)</h2>
            <p>Data pendukung penyusunan RPS. Hubungi Admin jika ada perubahan data utama.</p>
        </div>
        <button class="btn btn-warning"><i class="fas fa-paper-plane"></i> Usulkan Perubahan</button>
    </div>
</div>

<div class="card">
    <div class="card-title">Daftar Mata Kuliah Prodi</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama MK</th>
                    <th>SKS</th>
                    <th>Semester</th>
                    <th>Jenis</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>TIF101</td>
                    <td>Pemrograman Dasar</td>
                    <td>3</td>
                    <td>1</td>
                    <td>Inti Prodi</td>
                </tr>
                <tr>
                    <td>TIF205</td>
                    <td>Sistem Basis Data</td>
                    <td>4</td>
                    <td>3</td>
                    <td>Inti Prodi</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-title">CPL (Capaian Pembelajaran Lulusan)</div>
    <div class="cpl-list">
        <div class="cpl-item">
            <strong>CPL-01</strong>
            <p>Mampu menerapkan pemikiran logis, kritis, sistematis, dan inovatif dalam konteks pengembangan atau implementasi ilmu pengetahuan dan teknologi.</p>
        </div>
        <div class="cpl-item">
            <strong>CPL-02</strong>
            <p>Mampu menunjukkan kinerja mandiri, bermutu, dan terukur.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
