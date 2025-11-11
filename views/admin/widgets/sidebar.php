<?php
if (!isset($sessionManager) || $sessionManager->get('role') !== 'satpam') {
    header('Location: ../../index.php?action=login');
    exit;
}

$current_page = $current_page ?? '';
$logo_path = '../../images/head.png';
$fallback_logo = 'https://via.placeholder.com/80x80/ffffff/E57229?text=LOGO';
?>

<!-- LOAD SIDEBAR CSS (Hanya sekali) -->
<link href="../css/sidebar.css" rel="stylesheet">

<div class="sidebar-wrapper">
    <div class="sidebar">
        <!-- LOGO (Desktop & Tablet) -->
        <div class="logo-container d-none d-lg-flex">
            <img src="<?= $logo_path ?>"
                alt="Logo"
                class="logo-img"
                onerror="this.onerror=null; this.src='<?= $fallback_logo ?>';">
        </div>

        <!-- NAVIGASI UTAMA -->
        <div class="sidebar-btn">
            <a href="../../index.php?action=dashboard"
                class="sidebar-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">inbox</span>
                <span class="text">Kotak Masuk</span>
            </a>
            <a href="../../index.php?action=laporan_ditemukan_form"
                class="sidebar-item <?= $current_page === 'laporan_ditemukan' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">assignment</span>
                <span class="text">Laporan</span>
            </a>
            <a href="../../index.php?action=dashboard_klaim"
                class="sidebar-item <?= $current_page === 'klaim' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">checked_bag_question</span>
                <span class="text">Klaim</span>
            </a>

            <!-- TOMBOL KELUAR – DIPINDAH KE SINI, SAMA LEBAR -->
            <div class="bawah"> <a href="../../logout.php" class="sidebar-item btn-keluar d-none d-lg-flex">
                    <span class="material-symbols-outlined">chip_extraction</span>
                    <span class="text">Keluar</span>
                </a></div>

        </div>
    </div>
</div>