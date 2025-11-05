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
                    <button class="filter-btn active" data-filter="all">
                        Semua Laporan
                    </button>
                    <div class="filter-divider"></div>
                    <button class="filter-btn" data-filter="pending">
                        <span class="material-symbols-outlined">chronic</span>
                        Belum selesai
                    </button>
                    <button class="filter-btn" data-filter="completed">
                        <span class="material-symbols-outlined">check</span>
                        Selesai
                    </button>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="cards-grid">
                <?php
                // Ambil data dari DB
                $result = $laporanController->getLaporanUser();
                $laporanList = $result['success'] ? $result['laporan'] : [];

                if (empty($laporanList)): ?>
                    <div class="text-center py-5 w-100">
                        <p class="text-muted fs-5">Tidak ada laporan saat ini.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($laporanList as $item): ?>
                        <?php
                        $status = $item['status'] === 'selesai' ? 'completed' : 'pending';
                        $statusText = $item['status'] === 'selesai' ? 'Selesai' : 'Belum Selesai';

                        // Gambar
                        $imgSrc = !empty($item['foto'])
                            ? "/{$item['foto']}"
                            : 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';
                        ?>
                        <!-- CARD SESUAI FRONTEND -->
                        <a href="index.php?action=detail_laporan&id=<?= $item['id_laporan'] ?>"
                            class="text-decoration-none text-dark d-block">
                            <div class="card" data-status="<?= $status ?>">
                                <div class="card-image">
                                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                                        alt="<?= htmlspecialchars($item['nama_barang']) ?>">
                                    <span class="card-status status-<?= $status ?>">
                                        <?= $statusText ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">
                                        <?= htmlspecialchars($item['nama_barang']) ?>
                                    </h3>
                                    <div class="card-info">
                                        <div class="info-item">
                                            <span class="material-symbols-outlined">pin_drop</span>
                                            <span><?= htmlspecialchars($item['lokasi']) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="material-symbols-outlined">category</span>
                                            <span><?= ucfirst($item['kategori']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Filter functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');

                const filter = button.dataset.filter;

                cards.forEach(card => {
                    const cardStatus = card.dataset.status;

                    if (filter === 'all') {
                        card.parentElement.style.display = 'block';
                    } else if (filter === 'pending' && cardStatus === 'pending') {
                        card.parentElement.style.display = 'block';
                    } else if (filter === 'completed' && cardStatus === 'completed') {
                        card.parentElement.style.display = 'block';
                    } else {
                        card.parentElement.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>