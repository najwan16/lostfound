<?php
$logo_path     = '/public/assets/images/head.png';
$fallback_logo = 'https://via.placeholder.com/80x80/ffffff/E57229?text=LOGO';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Satpam - Lost & Found FILKOM' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/sidebar.css">
    <link rel="stylesheet" href="/public/assets/css/mail.css">
</head>

<body>

    <div class="sidebar-wrapper">
        <div class="sidebar">
            <div class="logo-container d-none d-lg-flex">
                <img src="<?= $logo_path ?>" alt="Logo" class="logo-img"
                    onerror="this.onerror=null;this.src='<?= $fallback_logo ?>';">
            </div>

            <div class="sidebar-btn">
                <a href="index.php?action=mail" class="sidebar-item <?= $current_page === 'mail' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">inbox</span>
                    <span class="text">Laporan Cocok</span>
                </a>
                <a href="index.php?action=dashboard" class="sidebar-item <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">assignment</span>
                    <span class="text">Laporan</span>
                </a>
                <a href="index.php?action=dashboard_claim" class="sidebar-item <?= $current_page === 'dashboard_claim' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">fact_check</span>
                    <span class="text">Klaim</span>
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

    <main class="main-content">
        <div class="page-container">