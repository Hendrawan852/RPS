<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="page-header">
    <h2>Data Master</h2>
    <p>Kelola referensi data yang digunakan dalam penyusunan RPS.</p>
</div>

<div class="master-tabs">
    <button class="tab-btn active" data-tab="mk">Mata Kuliah</button>
    <button class="tab-btn" data-tab="cpl">CPL & CPMK</button>
    <button class="tab-btn" data-tab="bk">Bahan Kajian</button>
    <button class="tab-btn" data-tab="metode">Metode & Referensi</button>
</div>

<div class="card tab-content active" id="mk">
    <div class="card-title">
        <span>Data Mata Kuliah</span>
        <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah MK</button>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Semester</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>TIF101</td>
                    <td>Pemrograman Dasar</td>
                    <td>3</td>
                    <td>1</td>
                    <td>Wajib</td>
                    <td>
                         <button class="btn-icon text-primary"><i class="fas fa-edit"></i></button>
                         <button class="btn-icon text-danger"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>TIF205</td>
                    <td>Sistem Basis Data</td>
                    <td>4</td>
                    <td>3</td>
                    <td>Wajib</td>
                    <td>
                         <button class="btn-icon text-primary"><i class="fas fa-edit"></i></button>
                         <button class="btn-icon text-danger"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card tab-content" id="cpl">
    <div class="card-title">Capaian Pembelajaran Lulusan (CPL)</div>
    <p>Kelola CPL dan CPMK di sini.</p>
</div>

<script>
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            tab.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
