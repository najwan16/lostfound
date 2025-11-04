<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehilangan - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/laporan.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- BANNER -->
        <div class="banner">
            <div class="left">
                <h1>Kehilangan barang</h1>
                <p>Segera laporkan kehilangan barang Anda melalui platform kami untuk mempermudah proses pencarian dan pengembalian.</p>
                <a class="btn-report <?= $currentPage === 'laporan_form' ? 'active' : '' ?>"
                    href="../index.php?action=laporan_form">Lapor kehilangan!</a>
            </div>
            <div class="right">
                <img src="../images/tangan.png" alt="Tangan memegang koin">
            </div>
        </div>

        <!-- KONTEN FILTER + WIDGET -->
        <div class="content-section mt-4">
            <div class="row">
                <!-- FILTER KIRI -->
                <div class="col-lg-3 col-md-4 mb-4">
                    <div class="filter-sidebar">
                        <button class="filter-btn active" data-filter="semua">
                            <i class="bi bi-list-ul"></i> Semua Laporan
                        </button>
                        <hr class="filter-divider">
                        <button class="filter-btn" data-filter="belum_ditemukan">
                            <i class="bi bi-hourglass-split"></i> Belum Selesai
                        </button>
                        <button class="filter-btn" data-filter="selesai">
                            <i class="bi bi-check-circle"></i> Selesai
                        </button>
                    </div>
                </div>

                <!-- WIDGET KANAN -->
                <div class="col-lg-9 col-md-8">
                    <?php
                    // -------------------------------------------------
                    // 1. Ambil data dari DB - HANYA MILIK USER
                    // -------------------------------------------------
                    $result = $laporanController->getLaporanUser();

                    if (!$result['success']) {
                        echo '<div class="alert alert-warning">' . $result['message'] . '</div>';
                        $laporanList = [];
                    } else {
                        $laporanList = $result['laporan'];
                    }

                    // Filter dari URL
                    $filter = $_GET['filter'] ?? 'semua';
                    $validFilters = ['semua', 'belum_ditemukan', 'selesai'];
                    if (!in_array($filter, $validFilters)) $filter = 'semua';

                    // Include widget
                    ob_start();
                    include __DIR__ . '/widgets/laporan_widget.php';
                    echo ob_get_clean();
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ---------- FILTER DENGAN JS (client-side) ----------
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                const url = new URL(window.location);
                url.searchParams.set('filter', filter);
                history.replaceState(null, '', url);

                // Render ulang widget via AJAX
                fetch(`../api/laporan_widget.php?filter=${filter}&user_only=1`)
                    .then(r => r.text())
                    .then(html => document.getElementById('laporan-container').parentElement.innerHTML = html);
            });
        });

        // Aktifkan filter dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const urlFilter = urlParams.get('filter') || 'semua';
        document.querySelector(`.filter-btn[data-filter="${urlFilter}"]`)?.click();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>