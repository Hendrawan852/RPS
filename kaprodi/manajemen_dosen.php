<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Manajemen Dosen Prodi</h2>
    <p>Pantau keterlibatan dan progress penyusunan RPS setiap dosen.</p>
</div>

<div class="card">
    <div class="card-title">Daftar Dosen di Program Studi Anda</div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Dosen</th>
                    <th>NIP / NIDN</th>
                    <th>MK Diajar</th>
                    <th>Status RPS</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="dosen-cell">
                            <img src="https://ui-avatars.com/api/?name=Hendrawan&background=random" alt="Avatar">
                            <div>
                                <strong>Dr. Hendrawan</strong>
                                <small>Lektor Kepala</small>
                            </div>
                        </div>
                    </td>
                    <td>198765432198765432</td>
                    <td>3 MK</td>
                    <td><span class="badge badge-success">3/3 Selesai</span></td>
                    <td>
                        <button class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane"></i> Kirim Notif</button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="dosen-cell">
                            <img src="https://ui-avatars.com/api/?name=Siti+Aminah&background=random" alt="Avatar">
                            <div>
                                <strong>Siti Aminah, M.Kom.</strong>
                                <small>Asisten Ahli</small>
                            </div>
                        </div>
                    </td>
                    <td>199988877766655544</td>
                    <td>2 MK</td>
                    <td><span class="badge badge-warning">1/2 On Progress</span></td>
                    <td>
                        <button class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane"></i> Kirim Notif</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>