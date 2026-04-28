<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Manajemen RPS Prodi</h2>
    <p>Arsip dan pencarian keseluruhan Rencana Pembelajaran Semester di program studi Anda.</p>
</div>

<div class="card">
    <div class="card-title">
        <span>Semua Arsip RPS</span>
        <div class="search-box">
            <input type="text" placeholder="Cari MK atau Dosen..." class="form-control-sm">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>MK / Kode</th>
                    <th>Dosen</th>
                    <th>Status</th>
                    <th>Tahun Ajaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Algoritma Pemrograman</strong><br><small>TIF102</small></td>
                    <td>Budi Santoso, M.T.</td>
                    <td><span class="badge badge-approved">Approved</span></td>
                    <td>2022/2023</td>
                    <td>
                        <div class="actions">
                            <button class="btn-icon" title="Download PDF"><i class="fas fa-file-pdf"></i></button>
                            <button class="btn-icon" title="Bandingkan Versi"><i class="fas fa-copy"></i></button>
                            <button class="btn-icon" title="Detail"><i class="fas fa-info-circle"></i></button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>Struktur Data</strong><br><small>TIF201</small></td>
                    <td>Dr. Hendrawan</td>
                    <td><span class="badge badge-archived">Archived</span></td>
                    <td>2021/2022</td>
                    <td>
                        <div class="actions">
                            <button class="btn-icon" title="Lihat Arsip"><i class="fas fa-archive"></i></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
