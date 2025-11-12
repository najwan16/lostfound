<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found Cards</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/laporan.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Banner -->
        <div class="banner">
            <div class="banner-left">
                <h1>Kehilangan barang</h1>
                <p>Segera laporkan kehilangan barang Anda melalui platform kami untuk mempermudah proses pencarian dan pengembalian.</p>
                <a href="index.php?action=laporan_form" class="btn-report">Lapor Kehilangan</a>
            </div>
            <div class="banner-right">
                <img src="../images/tangan.png" alt="Tangan memegang koin">
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Sidebar Filter -->
            <div class="sidebar">
                <div class="filter-box">
                    <?php
                    $currentFilter = $_GET['filter'] ?? 'semua';
                    $filters = [
                        'semua' => ['label' => 'Semua Laporan', 'icon' => 'list'],
                        'belum_ditemukan' => ['label' => 'Belum Ditemukan', 'icon' => 'chronic'],
                        'sudah_diambil' => ['label' => 'Sudah Diambil', 'icon' => 'check']
                    ];
                    ?>
                    <?php foreach ($filters as $key => $f): ?>
                        <a href="index.php?action=laporan&filter=<?= $key ?>"
                            class="filter-btn text-decoration-none <?= $currentFilter === $key ? 'active' : '' ?>">
                            <span class="material-symbols-outlined"><?= $f['icon'] ?></span>
                            <?= $f['label'] ?>
                        </a>
                    <?php endforeach; ?>
                    <!-- Setelah tombol "Lapor Kehilangan" -->
<div class="text-center mt-4">
    <a href="index.php?action=klaim_saya" class="btn btn-primary px-5">
        Lihat Pengajuan Klaim Saya
    </a>
</div>
                </div>
                
            </div>

            

            <!-- Cards Grid -->
            <div class="cards-grid">
                <?php
                // Ambil data user
                $result = $laporanController->getLaporanUser();
                $laporanList = $result['success'] ? $result['laporan'] : [];

                // Filter dari URL
                $filter = $_GET['filter'] ?? 'semua';

                // Include widget (filter otomatis di dalam)
                ob_start();
                include __DIR__ . '/widgets/laporan_widget.php';
                echo ob_get_clean();
                ?>
                
            </div>
        </div>
    </div>
</body>

</html>