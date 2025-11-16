    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lost & Found Cards</title>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
        <link href="public/assets/css/laporan.css" rel="stylesheet">
    </head>

    <body>
        <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>

        <div class="container">
            <!-- Banner -->
            <div class="banner">
                <div class="banner-left">
                    <h1>Kehilangan barang</h1>
                    <p>Segera laporkan kehilangan barang Anda melalui platform kami untuk mempermudah proses pencarian dan pengembalian.</p>
                    <a href="index.php?action=laporan-form" class="btn-report">Lapor Kehilangan</a>
                </div>
                <div class="banner-right">
                    <img src="public/assets/images/tangan.png" alt="Tangan memegang koin">
                </div>
            </div>

            <!-- Content -->
            <div class="content-wrapper">
                <!-- Sidebar Filter -->
                <div class="sidebar">
                    <div class="filter-box">
                        <?php
                        $currentFilter = $filter ?? 'semua';
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
                        <div class="text-center mt-4">
                            <a href="index.php?action=claim_saya" class="btn btn-primary px-5">
                                Lihat Pengajuan Klaim Saya
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="cards-grid">
                    <?php
                    // DATA SUDAH ADA DARI CONTROLLER: $laporanList & $filter
                    // JANGAN REDECLARE LAGI!
                    // HAPUS ob_start() → TIDAK DIPERLUKAN
                    include __DIR__ . '/../widgets/laporanCard.php';
                    ?>
                </div>
            </div>
        </div>

        <!-- DEBUG: HAPUS SETELAH SELESAI -->
        <!-- <pre><?php var_dump($laporanList, $filter); ?></pre> -->
    </body>

    </html>