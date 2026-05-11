<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../logo/logo-unsri.webp" alt="Logo Unsri" style="width: 42px; height: auto;">
            <span>RPS System</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo ($current_page == 'daftar_rps') ? 'active' : ''; ?>">
                <a href="daftar_rps.php"><i class="fas fa-file-alt"></i> <span>Daftar RPS Saya</span></a>
            </li>
            <?php
            // Fetch count of rejected RPS for badge
            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_rps WHERE dosen_id = ? AND status = 'Rejected'");
            $stmt_count->execute([$_SESSION['user_id']]);
            $reject_count = $stmt_count->fetchColumn();
            ?>
            <li class="<?php echo ($current_page == 'rps_yang_perlu_direvisi') ? 'active' : ''; ?>">
                <a href="rps_yang_perlu_direvisi.php">
                    <i class="fas fa-exclamation-circle"></i> 
                    <span>Perlu Revisi</span>
                    <?php if ($reject_count > 0): ?>
                        <span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 8px; font-size: 10px; margin-left: auto;"><?php echo $reject_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'data_master') ? 'active' : ''; ?>">
                <a href="data_master.php"><i class="fas fa-database"></i> <span>Data Master</span></a>
            </li>
            <li class="<?php echo ($current_page == 'arsip') ? 'active' : ''; ?>">
                <a href="arsip.php"><i class="fas fa-archive"></i> <span>Arsip & Referensi</span></a>
            </li>
            <li class="<?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
                <a href="profil.php"><i class="fas fa-user-circle"></i> <span>Profil</span></a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="../auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</aside>

<main class="main-content">
    <header class="topbar">
        <div class="toggle-sidebar"><i class="fas fa-bars"></i></div>
        <div class="user-profile">
            <div style="text-align: right; margin-right: 15px;">
                <div style="font-weight: 700; color: #1e293b;"><?php echo $_SESSION['nama_lengkap']; ?></div>
                <div style="font-size: 11px; color: #64748b; font-weight: 600;"><?php echo $_SESSION['jabatan'] ?: 'Dosen Pengampu'; ?></div>
            </div>
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 2)); ?></div>
        </div>
    </header>
    <div class="content-wrapper">
