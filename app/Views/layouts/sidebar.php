<?php
// app/Views/layouts/sidebar.php

// AMBIL DARI CONTROLLER VIA GLOBALS
$sessionManager = $GLOBALS['sessionManager'] ?? null;
$current_page   = $GLOBALS['current_page'] ?? '';

// CEK AKSES
if (!$sessionManager || $sessionManager->get('role') !== 'satpam') {
    header('Location: index.php?action=login');
    exit;
}

$logo_path     = '/public/assets/images/head.png';
$fallback_logo = 'https://via.placeholder.com/80x80/ffffff/E57229?text=LOGO';
?>

<div class="sidebar-wrapper">
    <div class="sidebar">
        <!-- LOGO -->
        <div class="logo-container d-none d-lg-flex">
            <img src="<?= $logo_path ?>" alt="Logo" class="logo-img"
                onerror="this.onerror=null; this.src='<?= $fallback_logo ?>';">
        </div>

        <!-- NAVIGASI -->
        <div class="sidebar-btn">
            <a href="index.php?action=mail"
                class="sidebar-item <?= $current_page === 'mail' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">inbox</span>
                <span class="text">Kotak Masuk</span>
            </a>
            <a href="index.php?action=dashboard"
                class="sidebar-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">assignment</span>
                <span class="text">Laporan</span>
            </a>
            <a href="index.php?action=dashboard_claim"
                class="sidebar-item <?= $current_page === 'dashboard_claim' ? 'active' : '' ?>">
                <span class="material-symbols-outlined">fact_check</span>
                <span class="text">Claim</span>
            </a>

            <div class="bawah">
                <a href="logout.php" class="sidebar-item btn-keluar d-none d-lg-flex">
                    <span class="material-symbols-outlined">chip_extraction</span>
                    <span class="text">Keluar</span>
                </a>
            </div>
        </div>
    </div>
</div>