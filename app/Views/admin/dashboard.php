<!-- views/admin/dashboard_satpam.php -->
<?php
// ANTI-CACHE
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once dirname(__DIR__, 2) . '../../config/db.php';
require_once dirname(__DIR__, 2) . '/controllers/AuthController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: ' . dirname(__DIR__, 2) . '/index.php?action=home');
    exit;
}

$pdo = getDB();

// === FILTER ===
$filter_hilang = $_GET['filter_hilang'] ?? 'semua';
$filter_ditemukan = $_GET['filter_ditemukan'] ?? 'semua';

// === AMBIL DATA HILANG ===
$where_hilang = "WHERE l.tipe_laporan = 'hilang'";
if ($filter_hilang !== 'semua') {
    $where_hilang .= " AND l.status = ?";
}
$stmt_hilang = $pdo->prepare("
    SELECT l.id_laporan, l.nama_barang, l.deskripsi_fisik, l.kategori, 
           l.lokasi, l.waktu, l.status, l.created_at,
           a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    $where_hilang
    ORDER BY l.created_at DESC
");
$stmt_hilang->execute($filter_hilang !== 'semua' ? [$filter_hilang] : []);
$laporan_hilang = $stmt_hilang->fetchAll();

// === AMBIL DATA DITEMUKAN ===
$where_ditemukan = "WHERE l.tipe_laporan = 'ditemukan'";
if ($filter_ditemukan !== 'semua') {
    $where_ditemukan .= " AND l.status = ?";
}
$stmt_ditemukan = $pdo->prepare("
    SELECT l.id_laporan, l.nama_barang, l.deskripsi_fisik, l.kategori, 
           l.lokasi, l.waktu, l.status, l.created_at,
           a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    $where_ditemukan
    ORDER BY l.created_at DESC
");
$stmt_ditemukan->execute($filter_ditemukan !== 'semua' ? [$filter_ditemukan] : []);
$laporan_ditemukan = $stmt_ditemukan->fetchAll();

$current_page = 'dashboard';
$page_title = 'Dashboard Satpam';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
        <link href="public/assets/css/admin.css" rel="stylesheet">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include realpath(dirname(__DIR__) . '/layouts/sidebar.php'); ?>


    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container-fluid p-4">

            <!-- TOMBOL LAPOR -->
            <div class="mb-4">
                <a href="../../index.php?action=laporanSatpam-form" class="btn-lapor-ditemukan d-block text-center text-decoration-none">
                    <div class="icon-plus">
                        <span class="material-symbols-outlined">add</span>
                    </div>
                    <span class="text-lapor">Buat Laporan Penemuan</span>
                </a>
            </div>

            <!-- TABEL: BARANG HILANG -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-header bg-gradient-warning text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Barang Hilang</h5>
                    <div class="filter-group">
                        <a href="?action=dashboard&filter_hilang=semua" class="btn btn-sm <?= $filter_hilang === 'semua' ? 'btn-light' : 'btn-outline-light' ?>">Semua</a>
                        <a href="?action=dashboard&filter_hilang=belum_ditemukan" class="btn btn-sm <?= $filter_hilang === 'belum_ditemukan' ? 'btn-light' : 'btn-outline-light' ?>">Belum Ditemukan</a>
                        <a href="?action=dashboard&filter_hilang=sudah_diambil" class="btn btn-sm <?= $filter_hilang === 'sudah_diambil' ? 'btn-light' : 'btn-outline-light' ?>">Sudah Diambil</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($laporan_hilang)): ?>
                        <div class="alert alert-info text-center">
                            Tidak ada laporan barang hilang.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Barang</th>
                                        <th>Deskripsi</th>
                                        <th>Kategori</th>
                                        <th>Lokasi</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Dibuat</th>
                                        <th>Pembuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan_hilang as $i => $l): ?>
                                        <tr class="clickable-row" data-href="../../index.php?action=laporanSatpam-detail&id=<?= $l['id_laporan'] ?>">
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                            <td class="text-truncate" style="max-width:150px;"><?= htmlspecialchars($l['deskripsi_fisik'] ?: '-') ?></td>
                                            <td><span class="badge bg-primary"><?= ucfirst($l['kategori']) ?></span></td>
                                            <td><?= htmlspecialchars($l['lokasi']) ?></td>
                                            <td><small><?= date('d M Y H:i', strtotime($l['waktu'])) ?></small></td>
                                            <td>
                                                <span class="badge <?= $l['status'] === 'belum_ditemukan' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $l['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                                            <td>
                                                <div class="pembuat">
                                                    <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($l['nomor_kontak']) ?></small>
                                                    <?php if ($l['nomor_induk']): ?>
                                                        <div class="nim">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TABEL: BARANG DITEMUKAN -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Barang Ditemukan</h5>
                    <div class="filter-group">
                        <a href="?action=dashboard&filter_ditemukan=semua" class="btn btn-sm <?= $filter_ditemukan === 'semua' ? 'btn-light' : 'btn-outline-light' ?>">Semua</a>
                        <a href="?action=dashboard&filter_ditemukan=menunggu_klaim" class="btn btn-sm <?= $filter_ditemukan === 'menunggu_klaim' ? 'btn-light' : 'btn-outline-light' ?>">Menunggu Klaim</a>
                        <a href="?action=dashboard&filter_ditemukan=sudah_diambil" class="btn btn-sm <?= $filter_ditemukan === 'sudah_diambil' ? 'btn-light' : 'btn-outline-light' ?>">Sudah Diambil</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($laporan_ditemukan)): ?>
                        <div class="alert alert-info text-center">
                            Tidak ada laporan barang ditemukan.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Barang</th>
                                        <th>Deskripsi</th>
                                        <th>Kategori</th>
                                        <th>Lokasi</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Dibuat</th>
                                        <th>Pembuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan_ditemukan as $i => $l): ?>
                                        <tr class="clickable-row" data-href="../../index.php?action=laporanSatpam-detail&id=<?= $l['id_laporan'] ?>">
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                            <td class="text-truncate" style="max-width:150px;"><?= htmlspecialchars($l['deskripsi_fisik'] ?: '-') ?></td>
                                            <td><span class="badge bg-primary"><?= ucfirst($l['kategori']) ?></span></td>
                                            <td><?= htmlspecialchars($l['lokasi']) ?></td>
                                            <td><small><?= date('d M Y H:i', strtotime($l['waktu'])) ?></small></td>
                                            <td>
                                                <span class="badge <?= $l['status'] === 'menunggu_klaim' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $l['status'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                                            <td>
                                                <div class="pembuat">
                                                    <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($l['nomor_kontak']) ?></small>
                                                    <?php if ($l['nomor_induk']): ?>
                                                        <div class="nim">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', () => {
                window.location = row.dataset.href;
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>