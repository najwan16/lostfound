<!-- admin/widget/sidebar.php -->
<?php
// Pastikan hanya satpam
if (!isset($sessionManager) || $sessionManager->get('role') !== 'satpam') {
    header('Location: ../../index.php?action=login');
    exit;
}

$current_page = $current_page ?? '';
?>

<div class="sidebar-wrapper">
    <div class="sidebar">
        <div class="atas">
            <img src="../../images/head.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40';">
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
            </div>
        </div>
        <div class="bawah">
            <a href="../../logout.php" class="btn-keluar">
                <span class="material-symbols-outlined">chip_extraction</span>
                <span class="text">Keluar</span>
            </a>
        </div>
    </div>
</div>